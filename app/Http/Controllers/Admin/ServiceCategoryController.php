<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\FiltersByStatus;
use App\Http\Controllers\Admin\Concerns\StoresUploadedImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceCategoryRequest;
use App\Models\ServiceCategory;
use App\Services\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class ServiceCategoryController extends Controller
{
    use FiltersByStatus, StoresUploadedImage;

    private const UPLOAD_FIELDS = ['image', 'remove_image', 'icon_image', 'remove_icon_image', 'status'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $status = $this->statusFilter($request);
        $counts = $this->statusCounts(ServiceCategory::query());

        $categories = ServiceCategory::query()
            ->tap(fn ($query) => $this->applyStatus($query, $status))
            ->withCount('activities')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'search', 'status', 'counts'));
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
                'image_path' => $path,
                'icon_image_path' => $iconPath,
            ]);
        } catch (Throwable $e) {
            $images->release($path);
            $images->release($iconPath);

            throw $e;
        }

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

        if (array_key_exists('image_path', $data)) {
            $images->release($oldPath);
        }

        if (array_key_exists('icon_image_path', $data)) {
            $images->release($oldIconPath);
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

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
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
