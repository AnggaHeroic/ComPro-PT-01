<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageContentRequest;
use App\Models\PageContent;
use App\Support\PageContents;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PageContentController extends Controller
{
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

    public function update(PageContentRequest $request, string $page): RedirectResponse
    {
        $submitted = $request->validated('content');

        // Hanya kolom yang terdaftar yang diproses; kunci lain diabaikan.
        foreach (PageContents::fields($page) as $key => $field) {
            $value = trim((string) ($submitted[$key] ?? ''));

            // Sama dengan teks bawaan: tidak perlu disimpan.
            if ($value === $field['default']) {
                PageContent::where(['page' => $page, 'key' => $key])->delete();

                continue;
            }

            PageContent::updateOrCreate(['page' => $page, 'key' => $key], ['value' => $value]);
        }

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Konten halaman berhasil disimpan.');
    }

    public function reset(string $page): RedirectResponse
    {
        abort_unless(PageContents::has($page), 404);

        PageContent::where('page', $page)->delete();

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Semua teks dikembalikan ke bawaan.');
    }
}
