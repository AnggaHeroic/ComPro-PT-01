<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersByStatus;
use App\Http\Controllers\Admin\Concerns\StoresUploadedImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BusinessActivityRequest;
use App\Http\Requests\Admin\ReorderItemsRequest;
use App\Models\ActivityLog;
use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use App\Services\ImageUploader;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class BusinessActivityController extends Controller
{
    use FiltersByStatus, StoresUploadedImage;

    private const NON_COLUMN_FIELDS = ['image', 'remove_image', 'status'];

    /** Nama kolom untuk riwayat aktivitas. */
    private const LOG_LABELS = [
        'service_category_id' => 'Kategori', 'kbli_code' => 'Kode KBLI', 'name' => 'Nama', 'slug' => 'Alamat URL',
        'short_description' => 'Deskripsi singkat', 'description' => 'Deskripsi detail', 'image_path' => 'Gambar',
        'is_published' => 'Status',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $categoryId = $request->integer('category') ?: null;
        $status = $this->statusFilter($request);
        $counts = $this->statusCounts(
            BusinessActivity::query()->when($categoryId, fn ($query) => $query->where('service_category_id', $categoryId))
        );

        // Mode atur urutan: tanpa pencarian/filter status; semua baris dimuat agar urutan per kategori utuh.
        $reorder = $request->boolean('reorder') && $search === '' && $status === null;

        $query = BusinessActivity::query()
            ->tap(fn ($query) => $this->applyStatus($query, $status))
            ->with('category:id,name,slug')
            ->withCount('images')
            ->when($categoryId, fn ($query) => $query->where('service_category_id', $categoryId))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('kbli_code', 'like', "%{$search}%");
            }))
            ->orderBy('service_category_id')
            ->ordered();

        $activities = $reorder ? $query->get() : $query->paginate(15)->withQueryString();

        return view('admin.activities.index', [
            'activities' => $activities,
            'categories' => ServiceCategory::ordered()->get(['id', 'name']),
            'search' => $search,
            'categoryId' => $categoryId,
            'status' => $status,
            'counts' => $counts,
            'reorder' => $reorder,
        ]);
    }

    public function create(Request $request): View
    {
        $categoryId = $request->integer('category') ?: null;

        $activity = new BusinessActivity([
            'service_category_id' => $categoryId,
            'sort_order' => $categoryId
                ? (int) BusinessActivity::where('service_category_id', $categoryId)->max('sort_order') + 1
                : 1,
            'is_published' => true,
        ]);

        return view('admin.activities.create', [
            'activity' => $activity,
            'categories' => ServiceCategory::ordered()->get(['id', 'name']),
        ]);
    }

    public function store(BusinessActivityRequest $request, ImageUploader $images): RedirectResponse
    {
        $path = $this->storeUploadedImage($request, $images, 'activities');

        try {
            $activity = BusinessActivity::create([
                ...$request->safe()->except(self::NON_COLUMN_FIELDS),
                'sort_order' => (int) BusinessActivity::where('service_category_id', $request->integer('service_category_id'))->max('sort_order') + 1,
                'image_path' => $path,
            ]);
        } catch (Throwable $e) {
            $images->release($path);

            throw $e;
        }

        $activity->loadMissing('category:id,name');
        ActivityLogger::record(ActivityLog::ACTION_CREATED, 'Aktivitas Usaha', "Menambahkan aktivitas \"{$activity->name}\" pada kategori \"{$activity->category?->name}\" dengan status ".ActivityLogger::status($activity->is_published));

        return $this->redirectAfterSave($activity, 'Aktivitas berhasil ditambahkan.');
    }

    public function edit(BusinessActivity $activity): View
    {
        return view('admin.activities.edit', [
            'activity' => $activity,
            'categories' => ServiceCategory::ordered()->get(['id', 'name']),
        ]);
    }

    public function update(BusinessActivityRequest $request, BusinessActivity $activity, ImageUploader $images): RedirectResponse
    {
        $data = $request->safe()->except(self::NON_COLUMN_FIELDS);
        $oldPath = $activity->image_path;
        $newPath = $this->storeUploadedImage($request, $images, 'activities');

        if ((int) $data['service_category_id'] !== $activity->service_category_id) {
            $data['sort_order'] = (int) BusinessActivity::where('service_category_id', $data['service_category_id'])->max('sort_order') + 1;
        }

        if ($newPath !== null) {
            $data['image_path'] = $newPath;
        } elseif ($request->boolean('remove_image')) {
            $data['image_path'] = null;
        }

        try {
            $activity->update($data);
        } catch (Throwable $e) {
            $images->release($newPath);

            throw $e;
        }

        $changed = ActivityLogger::changedLabels($activity, self::LOG_LABELS);

        if (array_key_exists('image_path', $data)) {
            $images->release($oldPath);
        }

        if ($changed !== '') {
            ActivityLogger::record(ActivityLog::ACTION_UPDATED, 'Aktivitas Usaha', "Mengubah aktivitas \"{$activity->name}\" (yang diubah: {$changed})");
        }

        return $this->redirectAfterSave($activity, 'Aktivitas berhasil diperbarui.');
    }

    public function destroy(BusinessActivity $activity, ImageUploader $images): RedirectResponse
    {
        // Baris galeri ikut terhapus oleh database (cascade); file fisiknya dibersihkan di sini.
        $paths = $activity->images()->pluck('image_path')->push($activity->image_path)->all();
        $categoryId = $activity->service_category_id;
        $categoryName = $activity->category()->value('name');

        $activity->delete();

        foreach ($paths as $path) {
            $images->release($path);
        }

        ActivityLogger::record(ActivityLog::ACTION_DELETED, 'Aktivitas Usaha', "Menghapus aktivitas \"{$activity->name}\" dari kategori \"{$categoryName}\"");

        return redirect()
            ->route('admin.activities.index', ['category' => $categoryId])
            ->with('success', 'Aktivitas berhasil dihapus.');
    }

    /**
     * Simpan urutan baru dari drag-and-drop untuk satu kategori.
     */
    public function reorder(ReorderItemsRequest $request): JsonResponse
    {
        $categoryId = $request->integer('category');
        $order = array_map('intval', $request->validated('order'));

        if (! ServiceCategory::whereKey($categoryId)->exists()) {
            throw ValidationException::withMessages(['category' => 'Kategori tidak ditemukan.']);
        }

        $expected = BusinessActivity::where('service_category_id', $categoryId)
            ->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();

        if ($expected !== collect($order)->sort()->values()->all()) {
            throw ValidationException::withMessages([
                'order' => 'Daftar aktivitas sudah berubah. Muat ulang halaman lalu coba lagi.',
            ]);
        }

        DB::transaction(function () use ($order, $categoryId) {
            foreach ($order as $position => $id) {
                BusinessActivity::where('service_category_id', $categoryId)->whereKey($id)->update(['sort_order' => $position + 1]);
            }
        });

        ActivityLogger::record(ActivityLog::ACTION_REORDERED, 'Aktivitas Usaha', 'Mengubah urutan aktivitas pada kategori "'.ServiceCategory::whereKey($categoryId)->value('name').'"');

        return response()->json(['message' => 'Urutan aktivitas disimpan.']);
    }

    /**
     * Draft diarahkan ke daftar draft (pada kategori yang sama) agar mudah ditemukan.
     */
    private function redirectAfterSave(BusinessActivity $activity, string $message): RedirectResponse
    {
        $params = ['category' => $activity->service_category_id];

        if ($activity->is_published) {
            return redirect()->route('admin.activities.index', $params)->with('success', $message);
        }

        return redirect()
            ->route('admin.activities.index', $params + ['status' => 'draft'])
            ->with('success', rtrim($message, '.').' sebagai draft. Belum tampil di website.');
    }
}
