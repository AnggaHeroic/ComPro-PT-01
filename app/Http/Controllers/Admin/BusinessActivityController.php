<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersByStatus;
use App\Http\Controllers\Admin\Concerns\StoresUploadedImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BusinessActivityRequest;
use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use App\Services\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class BusinessActivityController extends Controller
{
    use FiltersByStatus, StoresUploadedImage;

    private const NON_COLUMN_FIELDS = ['image', 'remove_image', 'status'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $categoryId = $request->integer('category') ?: null;
        $status = $this->statusFilter($request);
        $counts = $this->statusCounts(
            BusinessActivity::query()->when($categoryId, fn ($query) => $query->where('service_category_id', $categoryId))
        );

        $activities = BusinessActivity::query()
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
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        return view('admin.activities.index', [
            'activities' => $activities,
            'categories' => ServiceCategory::ordered()->get(['id', 'name']),
            'search' => $search,
            'categoryId' => $categoryId,
            'status' => $status,
            'counts' => $counts,
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
                'image_path' => $path,
            ]);
        } catch (Throwable $e) {
            $images->release($path);

            throw $e;
        }

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

        if (array_key_exists('image_path', $data)) {
            $images->release($oldPath);
        }

        return $this->redirectAfterSave($activity, 'Aktivitas berhasil diperbarui.');
    }

    public function destroy(BusinessActivity $activity, ImageUploader $images): RedirectResponse
    {
        // Baris galeri ikut terhapus oleh database (cascade); file fisiknya dibersihkan di sini.
        $paths = $activity->images()->pluck('image_path')->push($activity->image_path)->all();
        $categoryId = $activity->service_category_id;

        $activity->delete();

        foreach ($paths as $path) {
            $images->release($path);
        }

        return redirect()
            ->route('admin.activities.index', ['category' => $categoryId])
            ->with('success', 'Aktivitas berhasil dihapus.');
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
