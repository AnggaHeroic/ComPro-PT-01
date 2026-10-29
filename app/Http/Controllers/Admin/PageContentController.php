<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\StoresUploadedImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageContentRequest;
use App\Models\ActivityLog;
use App\Models\PageContent;
use App\Models\PageContentDefault;
use App\Models\ServiceCategory;
use App\Services\ImageUploader;
use App\Support\ActivityLogger;
use App\Support\PageContents;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
        $before = PageContent::where('page', $page)->pluck('value', 'key')->all();

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

                if (in_array($field['type'] ?? 'text', ['list', 'cards'], true)) {
                    $items = array_values(array_map(
                        fn ($item) => is_array($item)
                            ? ['title' => trim((string) ($item['title'] ?? '')), 'text' => trim((string) ($item['text'] ?? ''))]
                            : trim((string) $item),
                        (array) ($submitted[$key] ?? []),
                    ));

                    // Sama dengan bawaan: tidak perlu disimpan.
                    $value = $items === $content->defaultValue($key) ? '' : json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                    if ($value === '') {
                        PageContent::where(['page' => $page, 'key' => $key])->delete();
                    } else {
                        PageContent::updateOrCreate(['page' => $page, 'key' => $key], ['value' => $value]);
                    }

                    continue;
                }

                // Baris baru dari browser (\r\n) disamakan agar bisa dibandingkan dengan teks bawaan.
                $value = trim(str_replace(["\r\n", "\r"], "\n", (string) ($submitted[$key] ?? '')));

                // Sama dengan teks bawaan: tidak perlu disimpan.
                if ($value === $content->defaultValue($key)) {
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

        $this->logChanges($page, $before);

        // Tetap di bagian yang sedang diedit.
        $section = $request->input('bagian');
        $section = is_string($section) && array_key_exists($section, PageContents::definitions()[$page]['sections']) ? $section : null;

        return redirect()
            ->route('admin.pages.edit', array_filter(['page' => $page, 'bagian' => $section]))
            ->with('success', 'Konten halaman berhasil disimpan.');
    }

    /**
     * Pratinjau satu bagian halaman dengan isian form yang belum disimpan. Halaman publik
     * dirender biasa (dengan isian sementara), lalu skrip kecil menyembunyikan semua bagian
     * lain sehingga hanya bagian yang diminta yang terlihat. Tidak ada yang disimpan.
     */
    public function preview(Request $request, string $page, string $section): Response
    {
        abort_unless(PageContents::has($page) && isset(PageContents::definitions()[$page]['sections'][$section]), 404);

        PageContents::beginPreview($page, (array) $request->input('content', []));

        try {
            $html = match ($page) {
                'home' => view('pages.home'),
                'about' => view('pages.about'),
                'contact' => view('pages.contact'),
                'services' => view('services.index', ['categories' => ServiceCategory::published()->ordered()->get()]),
            };
            $html = $html->render();
        } finally {
            PageContents::endPreview();
        }

        $isolate = view('admin.pages.preview-isolate', ['section' => $section])->render();
        $html = str_contains($html, '</body>')
            ? substr_replace($html, $isolate, strrpos($html, '</body>'), 0)
            : $html.$isolate;

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8')->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * Catat bagian halaman yang isinya berubah (dibanding sebelum disimpan).
     *
     * @param  array<string, string>  $before
     */
    private function logChanges(string $page, array $before): void
    {
        $after = PageContent::where('page', $page)->pluck('value', 'key')->all();
        $definition = PageContents::definitions()[$page];
        $sections = [];

        foreach ($definition['sections'] as $section) {
            foreach (array_keys($section['fields']) as $key) {
                if (($before[$key] ?? null) !== ($after[$key] ?? null)) {
                    $sections[$section['label']] = true;
                }
            }
        }

        if ($sections !== []) {
            ActivityLogger::record(ActivityLog::ACTION_UPDATED, 'Konten Halaman', 'Mengubah konten halaman '.$definition['label'].' (bagian yang diubah: '.implode(', ', array_keys($sections)).')', subject: 'page:'.$page);
        }
    }

    /**
     * Jadikan isi halaman yang sudah tersimpan sebagai setelan default baru. Tampilan website
     * tidak berubah; hanya acuan untuk "Reset default" yang berganti.
     */
    public function setDefault(string $page, ImageUploader $images): RedirectResponse
    {
        abort_unless(PageContents::has($page), 404);

        $content = PageContents::for($page);
        $stored = $content->storedValues();
        $oldDefaults = $content->defaultValues();
        $new = [];

        foreach (PageContents::fields($page) as $key => $field) {
            $raw = $stored[$key] ?? $oldDefaults[$key] ?? null;

            if ($raw === null) {
                continue;
            }

            $type = $field['type'] ?? 'text';

            // Yang sama dengan teks bawaan sistem tidak perlu disimpan sebagai default kustom.
            if (in_array($type, ['list', 'cards'], true) && json_decode($raw, true) === ($field['default'] ?? null)) {
                continue;
            }

            if (! in_array($type, ['list', 'cards', 'image'], true) && $raw === (string) ($field['default'] ?? '')) {
                continue;
            }

            $new[$key] = $raw;
        }

        DB::transaction(function () use ($page, $new) {
            PageContentDefault::where('page', $page)->delete();

            foreach ($new as $key => $value) {
                PageContentDefault::create(['page' => $page, 'key' => $key, 'value' => $value]);
            }

            // Isi tersimpan kini identik dengan default, jadi barisnya tidak diperlukan lagi.
            PageContent::where('page', $page)->delete();
        });

        $this->releaseImages($page, $images, $stored + $oldDefaults);

        ActivityLogger::record(ActivityLog::ACTION_DEFAULT, 'Konten Halaman', 'Menetapkan konten halaman '.PageContents::definitions()[$page]['label'].' yang tersimpan sebagai setelan default baru');

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Konten tersimpan saat ini dijadikan setelan default.');
    }

    /**
     * Hapus setelan default kustom (kembali ke teks bawaan sistem) tanpa mengubah tampilan website:
     * isi yang selama ini hanya mengikuti default kustom dipindahkan menjadi isi tersimpan.
     */
    public function clearDefault(string $page, ImageUploader $images): RedirectResponse
    {
        abort_unless(PageContents::has($page), 404);

        $content = PageContents::for($page);
        $defaults = $content->defaultValues();

        if ($defaults === []) {
            return redirect()->route('admin.pages.edit', $page)->with('success', 'Halaman ini sudah memakai setelan default bawaan sistem.');
        }

        $stored = $content->storedValues();

        DB::transaction(function () use ($page, $defaults, $stored) {
            foreach ($defaults as $key => $value) {
                if (! array_key_exists($key, $stored)) {
                    PageContent::create(['page' => $page, 'key' => $key, 'value' => $value]);
                }
            }

            PageContentDefault::where('page', $page)->delete();
        });

        $this->releaseImages($page, $images, $defaults);

        ActivityLogger::record(ActivityLog::ACTION_RESET, 'Konten Halaman', 'Menghapus setelan default yang ditetapkan admin untuk halaman '.PageContents::definitions()[$page]['label'].' (kembali ke default bawaan sistem)');

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Setelan default kustom dihapus. Tampilan website tidak berubah.');
    }

    /**
     * Hapus file gambar yang tidak lagi dipakai (release() memeriksa semua referensi).
     *
     * @param  array<string, string>  $values  kunci => nilai mentah
     */
    private function releaseImages(string $page, ImageUploader $images, array $values): void
    {
        foreach (PageContents::fields($page) as $key => $field) {
            if (($field['type'] ?? 'text') === 'image' && isset($values[$key])) {
                $images->release($values[$key]);
            }
        }
    }

    public function reset(string $page, ImageUploader $images): RedirectResponse
    {
        abort_unless(PageContents::has($page), 404);

        $content = PageContents::for($page);
        $custom = $content->hasCustomDefaults();
        $paths = collect(PageContents::fields($page))
            ->filter(fn ($field) => ($field['type'] ?? 'text') === 'image')
            ->keys()
            ->map(fn ($key) => $content->customImagePath($key))
            ->filter();

        PageContent::where('page', $page)->delete();

        $paths->each(fn ($path) => $images->release($path));

        ActivityLogger::record(ActivityLog::ACTION_RESET, 'Konten Halaman', 'Mengembalikan seluruh konten halaman '.PageContents::definitions()[$page]['label'].' ke setelan default '.($custom ? 'yang ditetapkan admin' : 'bawaan sistem'), subject: 'page:'.$page);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Semua teks dan gambar dikembalikan ke setelan default.');
    }
}
