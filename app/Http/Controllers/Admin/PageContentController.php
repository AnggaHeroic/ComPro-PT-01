<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\StoresUploadedImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageContentRequest;
use App\Models\PageContent;
use App\Services\ImageUploader;
use App\Support\PageContents;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

class PageContentController extends Controller
{
    use StoresUploadedImage;

    public function index(): View
    {
        return view('admin.pages.index', ['pages' => PageContents::definitions()]);
    }

    public function edit(string $page): View
    {
        abort_unless(PageContents::has($page), 404);

        return view('admin.pages.edit', [
            'page' => $page,
            'definition' => PageContents::definitions()[$page],
            'content' => PageContents::for($page),
        ]);
    }

    public function update(PageContentRequest $request, string $page, ImageUploader $images): RedirectResponse
    {
        $submitted = $request->validated('content') ?? [];
        $content = PageContents::for($page);

        $newPaths = [];
        $oldPaths = [];

        try {
            // Hanya kolom yang terdaftar yang diproses; kunci lain diabaikan.
            foreach (PageContents::fields($page) as $key => $field) {
                if (($field['type'] ?? 'text') === 'image') {
                    $old = $content->customImagePath($key);
                    $new = $this->storeUploadedImage($request, $images, 'pages', "image_{$key}");

                    if ($new !== null) {
                        $newPaths[] = $new;
                        PageContent::updateOrCreate(['page' => $page, 'key' => $key], ['value' => $new]);
                        $oldPaths[] = $old;
                    } elseif ($request->boolean("remove_image_{$key}") && $old !== null) {
                        // Kembali ke gambar bawaan.
                        PageContent::where(['page' => $page, 'key' => $key])->delete();
                        $oldPaths[] = $old;
                    }

                    continue;
                }

                $value = trim((string) ($submitted[$key] ?? ''));

                // Sama dengan teks bawaan: tidak perlu disimpan.
                if ($value === $field['default']) {
                    PageContent::where(['page' => $page, 'key' => $key])->delete();

                    continue;
                }

                PageContent::updateOrCreate(['page' => $page, 'key' => $key], ['value' => $value]);
            }
        } catch (Throwable $e) {
            foreach ($newPaths as $path) {
                $images->release($path);
            }

            throw $e;
        }

        foreach ($oldPaths as $path) {
            $images->release($path);
        }

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Konten halaman berhasil disimpan.');
    }

    public function reset(string $page, ImageUploader $images): RedirectResponse
    {
        abort_unless(PageContents::has($page), 404);

        $content = PageContents::for($page);
        $paths = collect(PageContents::fields($page))
            ->filter(fn ($field) => ($field['type'] ?? 'text') === 'image')
            ->keys()
            ->map(fn ($key) => $content->customImagePath($key))
            ->filter();

        PageContent::where('page', $page)->delete();

        $paths->each(fn ($path) => $images->release($path));

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Semua teks dan gambar dikembalikan ke bawaan.');
    }
}
