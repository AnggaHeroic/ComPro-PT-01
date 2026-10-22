<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersByStatus;
use App\Http\Controllers\Admin\Concerns\StoresUploadedImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderItemsRequest;
use App\Http\Requests\Admin\ServiceCategoryRequest;
use App\Models\ActivityLog;
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

class ServiceCategoryController extends Controller
{
    use FiltersByStatus, StoresUploadedImage;

    private const UPLOAD_FIELDS = ['image', 'remove_image', 'icon_image', 'remove_icon_image', 'status'];

    /** Nama kolom untuk riwayat aktivitas. */
    private const LOG_LABELS = [
        'name' => 'Nama', 'slug' => 'Alamat URL', 'description' => 'Deskripsi', 'image_path' => 'Gambar',
        'icon' => 'Ikon', 'icon_image_path' => 'Ikon kustom', 'is_published' => 'Status',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $status = $this->statusFilter($request);
        $counts = $this->statusCounts(ServiceCategory::query());

        // Mode atur urutan: hanya tanpa pencarian/filter, memuat semua baris agar urutan utuh.
        $reorder = $request->boolean('reorder') && $search === '' && $status === null;

        $query = ServiceCategory::query()
            ->tap(fn ($query) => $this->applyStatus($query, $status))
            ->withCount('activities')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->ordered();

        $categories = $reorder ? $query->get() : $query->paginate(15)->withQueryString();

        return view('admin.categories.index', compact('categories', 'search', 'status', 'counts', 'reorder'));
    }

    public function create(): View
    {
        $category = new ServiceCategory([
            'sort_order' => (int) ServiceCategory::max('sort_order') + 1,
            'is_published' => true,
        ]);

        return view('admin.categories.create', compact('category'));
    }

    public function store(ServiceCategoryRequest $request, ImageUploader $images): RedirectResponse
    {
        $path = $this->storeUploadedImage($request, $images, 'categories');

        try {
            $iconPath = $this->storeUploadedImage($request, $images, 'category-icons', 'icon_image', 256);
        } catch (Throwable $e) {
            $images->release($path);

            throw $e;
        }

        try {
            $category = ServiceCategory::create([
                ...$request->safe()->except(self::UPLOAD_FIELDS),
                'sort_order' => (int) ServiceCategory::max('sort_order') + 1,
                'image_path' => $path,
                'icon_image_path' => $iconPath,
            ]);
        } catch (Throwable $e) {
            $images->release($path);
            $images->release($iconPath);

            throw $e;
        }

        ActivityLogger::record(ActivityLog::ACTION_CREATED, 'Kategori', "Menambahkan kategori \"{$category->name}\" dengan status ".ActivityLogger::status($category->is_published));

        return $this->redirectAfterSave($category, 'Kategori berhasil ditambahkan.');
    }

    public function edit(ServiceCategory $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(ServiceCategoryRequest $request, ServiceCategory $category, ImageUploader $images): RedirectResponse
    {
        $data = $request->safe()->except(self::UPLOAD_FIELDS);
        $oldPath = $category->image_path;
        $oldIconPath = $category->icon_image_path;
        $newPath = $this->storeUploadedImage($request, $images, 'categories');

        try {
            $newIconPath = $this->storeUploadedImage($request, $images, 'category-icons', 'icon_image', 256);
        } catch (Throwable $e) {
            $images->release($newPath);

            throw $e;
        }

        if ($newPath !== null) {
            $data['image_path'] = $newPath;
        } elseif ($request->boolean('remove_image')) {
            $data['image_path'] = null;
        }

        if ($newIconPath !== null) {
            $data['icon_image_path'] = $newIconPath;
        } elseif ($request->boolean('remove_icon_image')) {
            $data['icon_image_path'] = null;
        }

        try {
            $category->update($data);
        } catch (Throwable $e) {
            $images->release($newPath);
            $images->release($newIconPath);

            throw $e;
        }

        $changed = ActivityLogger::changedLabels($category, self::LOG_LABELS);

        if (array_key_exists('image_path', $data)) {
            $images->release($oldPath);
        }

        if (array_key_exists('icon_image_path', $data)) {
            $images->release($oldIconPath);
        }

        if ($changed !== '') {
            ActivityLogger::record(ActivityLog::ACTION_UPDATED, 'Kategori', "Mengubah kategori \"{$category->name}\" (yang diubah: {$changed})");
        }

        return $this->redirectAfterSave($category, 'Kategori berhasil diperbarui.');
    }

    public function destroy(ServiceCategory $category, ImageUploader $images): RedirectResponse
    {
        $count = $category->activities()->count();

        if ($count > 0) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', "Kategori \"{$category->name}\" masih memiliki {$count} aktivitas. Pindahkan atau hapus aktivitasnya terlebih dahulu.");
        }

        $category->delete();
        $images->release($category->image_path);
        $images->release($category->icon_image_path);

        ActivityLogger::record(ActivityLog::ACTION_DELETED, 'Kategori', "Menghapus kategori \"{$category->name}\"");

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    /**
     * Simpan urutan baru dari drag-and-drop. Daftar id harus persis semua kategori.
     */
    public function reorder(ReorderItemsRequest $request): JsonResponse
    {
        $order = array_map('intval', $request->validated('order'));

        $expected = ServiceCategory::query()->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();

        if ($expected !== collect($order)->sort()->values()->all()) {
            throw ValidationException::withMessages([
                'order' => 'Daftar kategori sudah berubah. Muat ulang halaman lalu coba lagi.',
            ]);
        }

        DB::transaction(function () use ($order) {
            foreach ($order as $position => $id) {
                ServiceCategory::whereKey($id)->update(['sort_order' => $position + 1]);
            }
        });

        ActivityLogger::record(ActivityLog::ACTION_REORDERED, 'Kategori', 'Mengubah urutan kategori');

        return response()->json(['message' => 'Urutan kategori disimpan.']);
    }

    /**
     * Draft diarahkan ke daftar draft agar langsung terlihat dan mudah ditemukan.
     */
    private function redirectAfterSave(ServiceCategory $category, string $message): RedirectResponse
    {
        if ($category->is_published) {
            return redirect()->route('admin.categories.index')->with('success', $message);
        }

        return redirect()
            ->route('admin.categories.index', ['status' => 'draft'])
            ->with('success', rtrim($message, '.').' sebagai draft. Belum tampil di website.');
    }
}
