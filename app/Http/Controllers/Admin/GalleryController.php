<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderGalleryRequest;
use App\Http\Requests\Admin\StoreGalleryImagesRequest;
use App\Http\Requests\Admin\UpdateGalleryImageRequest;
use App\Models\ActivityImage;
use App\Models\ActivityLog;
use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use App\Services\ImageUploader;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class GalleryController extends Controller
{
    private const DIRECTORY = 'activities/gallery';

    /**
     * Daftar aktivitas beserta jumlah foto galerinya.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $categoryId = $request->integer('category') ?: null;

        $activities = BusinessActivity::query()
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

        return view('admin.gallery.index', [
            'activities' => $activities,
            'categories' => ServiceCategory::ordered()->get(['id', 'name']),
            'search' => $search,
            'categoryId' => $categoryId,
        ]);
    }

    /**
     * Kelola galeri satu aktivitas.
     */
    public function show(BusinessActivity $activity): View
    {
        $activity->load(['category', 'images']);

        return view('admin.gallery.show', [
            'activity' => $activity,
            'images' => $activity->images,
            'maxPerActivity' => (int) config('images.gallery_max_per_activity'),
            'maxPerUpload' => (int) config('images.gallery_max_per_upload'),
        ]);
    }

    /**
     * Unggah satu atau beberapa foto. Semua-atau-tidak-sama-sekali: bila ada
     * satu file gagal diproses, file lain yang sudah tersimpan dibersihkan.
     */
    public function store(StoreGalleryImagesRequest $request, BusinessActivity $activity, ImageUploader $images): RedirectResponse
    {
        $files = $request->file('images');
        $max = (int) config('images.gallery_max_per_activity');
        $current = $activity->images()->count();

        if ($current + count($files) > $max) {
            throw ValidationException::withMessages([
                'images' => "Galeri maksimal {$max} foto. Saat ini sudah ada {$current} foto, sisa kapasitas ".max(0, $max - $current).' foto.',
            ]);
        }

        $paths = [];

        try {
            foreach ($files as $file) {
                $paths[] = $images->store($file, self::DIRECTORY);
            }
        } catch (InvalidArgumentException|RuntimeException $e) {
            $this->releaseAll($images, $paths);
            report($e);

            throw ValidationException::withMessages([
                'images' => 'Salah satu file tidak dapat diproses. Gunakan file JPG, PNG, atau WebP yang valid. Tidak ada foto yang disimpan.',
            ]);
        }

        try {
            DB::transaction(function () use ($activity, $paths) {
                $order = (int) $activity->images()->max('sort_order');

                foreach ($paths as $index => $path) {
                    $activity->images()->create([
                        'image_path' => $path,
                        'sort_order' => $order + $index + 1,
                    ]);
                }
            });
        } catch (Throwable $e) {
            $this->releaseAll($images, $paths);

            throw $e;
        }

        ActivityLogger::record(ActivityLog::ACTION_CREATED, 'Galeri', "Menambahkan foto ke galeri aktivitas \"{$activity->name}\" (".count($paths).' foto)');

        return redirect()
            ->route('admin.gallery.show', $activity)
            ->with('success', count($paths).' foto berhasil ditambahkan.');
    }

    /**
     * Ubah caption dan/atau ganti file foto.
     */
    public function update(UpdateGalleryImageRequest $request, BusinessActivity $activity, ActivityImage $image, ImageUploader $images): RedirectResponse
    {
        abort_unless($image->business_activity_id === $activity->id, 404);

        $data = ['caption' => $request->validated('caption')];
        $oldPath = $image->image_path;
        $newPath = null;

        if ($request->hasFile('image')) {
            try {
                $newPath = $images->store($request->file('image'), self::DIRECTORY);
            } catch (InvalidArgumentException|RuntimeException $e) {
                report($e);

                throw ValidationException::withMessages([
                    'image' => 'Foto pengganti tidak dapat diproses. Gunakan file JPG, PNG, atau WebP yang valid.',
                ])->errorBag('image'.$image->id);
            }

            $data['image_path'] = $newPath;
        }

        try {
            $image->update($data);
        } catch (Throwable $e) {
            $images->release($newPath);

            throw $e;
        }

        if ($newPath !== null) {
            $images->release($oldPath);
        }

        $changed = ActivityLogger::changedLabels($image, ['caption' => 'Deskripsi', 'image_path' => 'Foto']);

        if ($changed !== '') {
            ActivityLogger::record(ActivityLog::ACTION_UPDATED, 'Galeri', "Mengubah foto di galeri aktivitas \"{$activity->name}\" (yang diubah: {$changed})");
        }

        return redirect()
            ->route('admin.gallery.show', $activity)
            ->with('success', 'Foto berhasil diperbarui.');
    }

    public function destroy(BusinessActivity $activity, ActivityImage $image, ImageUploader $images): RedirectResponse
    {
        abort_unless($image->business_activity_id === $activity->id, 404);

        $path = $image->image_path;
        $image->delete();
        $images->release($path);

        ActivityLogger::record(ActivityLog::ACTION_DELETED, 'Galeri', "Menghapus foto dari galeri aktivitas \"{$activity->name}\" (1 foto)");

        return redirect()
            ->route('admin.gallery.show', $activity)
            ->with('success', 'Foto berhasil dihapus.');
    }

    /**
     * Simpan urutan baru. Daftar id harus persis sama dengan foto yang ada.
     */
    public function reorder(ReorderGalleryRequest $request, BusinessActivity $activity): RedirectResponse
    {
        $order = array_map('intval', $request->validated('order'));

        $expected = $activity->images()->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $given = collect($order)->sort()->values()->all();

        if ($expected !== $given) {
            throw ValidationException::withMessages([
                'order' => 'Daftar foto sudah berubah. Muat ulang halaman lalu coba lagi.',
            ]);
        }

        DB::transaction(function () use ($activity, $order) {
            foreach ($order as $position => $id) {
                $activity->images()->whereKey($id)->update(['sort_order' => $position + 1]);
            }
        });

        ActivityLogger::record(ActivityLog::ACTION_REORDERED, 'Galeri', "Mengubah urutan foto di galeri aktivitas \"{$activity->name}\"");

        return redirect()
            ->route('admin.gallery.show', $activity)
            ->with('success', 'Urutan foto disimpan.');
    }

    /**
     * @param  list<string>  $paths
     */
    private function releaseAll(ImageUploader $images, array $paths): void
    {
        foreach ($paths as $path) {
            $images->release($path);
        }
    }
}
