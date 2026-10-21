<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\PageContent;
use App\Models\PageContentDefault;
use App\Models\User;
use App\Support\PageContents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageDefaultsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->create());
    }

    /** @return array<string, array<string, mixed>> */
    private function texts(array $overrides = []): array
    {
        $texts = [];
        foreach (PageContents::fields('about') as $key => $field) {
            if (($field['type'] ?? 'text') !== 'image') {
                $texts[$key] = $field['default'];
            }
        }

        return ['content' => array_merge($texts, $overrides)];
    }

    public function test_the_form_offers_the_default_actions_and_guests_are_blocked(): void
    {
        $this->get('/admin/pages/about')
            ->assertOk()
            ->assertSee('Setelan default')
            ->assertSee('Halaman ini memakai setelan default bawaan sistem.')
            ->assertSee('Setel default')
            ->assertSee('Reset default')
            ->assertDontSee('Pakai default bawaan sistem');

        auth()->logout();
        $this->post('/admin/pages/about/default')->assertRedirect('/login');
        $this->delete('/admin/pages/about/default')->assertRedirect('/login');
    }

    public function test_saved_content_becomes_the_new_default_without_changing_the_website(): void
    {
        $this->put('/admin/pages/about', $this->texts([
            'hero_label' => 'Profil Kami',
            'mission_items' => ['Misi default satu', 'Misi default dua'],
            'objective_items' => [['title' => 'Tujuan Default', 'text' => 'Uraian default.']],
        ]) + ['image_hero_image' => UploadedFile::fake()->image('default.jpg', 800, 500)]);
        $heroPath = PageContent::where('key', 'hero_image')->value('value');

        auth()->logout();
        $before = $this->get('/tentang-kami')->getContent();
        $this->actingAs(User::first());

        $this->post('/admin/pages/about/default')->assertRedirect('/admin/pages/about')->assertSessionHas('success');

        // Isi tersimpan dipindahkan ke default; tampilan publik sama persis.
        $this->assertSame(0, PageContent::where('page', 'about')->count());
        $this->assertSame('Profil Kami', PageContentDefault::where('key', 'hero_label')->value('value'));
        $this->assertSame($heroPath, PageContentDefault::where('key', 'hero_image')->value('value'));
        Storage::disk('public')->assertExists($heroPath);
        auth()->logout();
        $this->assertSame($before, $this->get('/tentang-kami')->getContent());

        $this->actingAs(User::first());
        $this->get('/admin/pages/about')->assertSee('ditetapkan oleh admin')->assertSee('Pakai default bawaan sistem');

        $log = ActivityLog::where('menu', 'Konten Halaman')->latest('id')->first();
        $this->assertSame(['default', 'Konten Halaman'], [$log->action, $log->menu]);
        $this->assertStringContainsString('setelan default baru ditetapkan', $log->detail);
    }

    public function test_reset_returns_to_the_custom_default_including_lists_and_photos(): void
    {
        $this->put('/admin/pages/about', $this->texts([
            'hero_label' => 'Profil Kami',
            'mission_items' => ['Misi default satu'],
        ]) + ['image_hero_image' => UploadedFile::fake()->image('default.jpg')]);
        $heroPath = PageContent::where('key', 'hero_image')->value('value');
        $this->post('/admin/pages/about/default');

        // Ubah semuanya, termasuk foto.
        $this->put('/admin/pages/about', $this->texts([
            'hero_label' => 'Label Lain',
            'mission_items' => ['Misi lain'],
        ]) + ['image_hero_image' => UploadedFile::fake()->image('lain.png')]);
        $otherPath = PageContent::where('key', 'hero_image')->value('value');
        $this->assertNotSame($heroPath, $otherPath);
        // Foto default tetap ada walau foto aktif diganti.
        Storage::disk('public')->assertExists($heroPath);

        auth()->logout();
        $changed = $this->get('/tentang-kami')->getContent();
        $this->assertStringContainsString('Label Lain', $changed);

        $this->actingAs(User::first());
        $this->delete('/admin/pages/about')->assertRedirect('/admin/pages/about');

        auth()->logout();
        $html = $this->get('/tentang-kami')->getContent();
        $this->assertStringContainsString('Profil Kami', $html);
        $this->assertStringContainsString('Misi default satu', $html);
        $this->assertStringNotContainsString('Label Lain', $html);
        $this->assertStringContainsString(Storage::disk('public')->url($heroPath), $html);
        // Foto yang tidak lagi dipakai dibersihkan; foto default tetap.
        Storage::disk('public')->assertMissing($otherPath);
        Storage::disk('public')->assertExists($heroPath);
    }

    public function test_values_equal_to_the_custom_default_are_not_stored_again(): void
    {
        $this->put('/admin/pages/about', $this->texts(['hero_label' => 'Profil Kami', 'mission_items' => ['Satu', 'Dua']]));
        $this->post('/admin/pages/about/default');

        $this->put('/admin/pages/about', $this->texts(['hero_label' => 'Profil Kami', 'mission_items' => ['Satu', 'Dua']]))
            ->assertSessionHasNoErrors();
        $this->assertSame(0, PageContent::where('page', 'about')->count());

        // Mengembalikan ke teks bawaan sistem kini dianggap perubahan dari default kustom.
        $defaults = $this->texts();
        $this->put('/admin/pages/about', $defaults);
        $this->assertSame('Tentang Kami', PageContents::for('about')->get('hero_label'));
        $this->assertSame(1, PageContent::where('key', 'hero_label')->count());
    }

    public function test_clearing_the_custom_default_keeps_the_website_unchanged_and_resets_to_the_system_text(): void
    {
        $this->put('/admin/pages/about', $this->texts(['hero_label' => 'Profil Kami', 'mission_items' => ['Satu', 'Dua']])
            + ['image_hero_image' => UploadedFile::fake()->image('d.jpg')]);
        $heroPath = PageContent::where('key', 'hero_image')->value('value');
        $this->post('/admin/pages/about/default');

        auth()->logout();
        $before = $this->get('/tentang-kami')->getContent();
        $this->actingAs(User::first());

        $this->delete('/admin/pages/about/default')->assertRedirect('/admin/pages/about')->assertSessionHas('success');

        $this->assertSame(0, PageContentDefault::count());
        auth()->logout();
        $this->assertSame($before, $this->get('/tentang-kami')->getContent());
        Storage::disk('public')->assertExists($heroPath);

        // Setelah itu "Reset default" berarti teks bawaan sistem.
        $this->actingAs(User::first());
        $this->delete('/admin/pages/about');
        auth()->logout();
        $html = $this->get('/tentang-kami')->getContent();
        $this->assertStringNotContainsString('Profil Kami', $html);
        $this->assertStringContainsString('photo-1497366811353', $html);
        Storage::disk('public')->assertMissing($heroPath);

        $this->actingAs(User::first());
        // Menghapus lagi saat sudah tidak ada default kustom tidak mencatat apa pun.
        $this->delete('/admin/pages/about/default')->assertSessionHas('success');
        $this->assertSame(1, ActivityLog::where('action', 'default')->count());
        // Menghapus default kustom juga dicatat sebagai "Reset default" (satu aksi untuk semua reset).
        $revert = ActivityLog::where('menu', 'Konten Halaman')->where('action', 'reset')->where('detail', 'like', '%default semula%')->first();
        $this->assertStringContainsString('dikembalikan ke default semula', $revert->detail);
        $this->assertSame('Reset default', $revert->actionLabel());
        $this->assertSame(0, ActivityLog::where('action', 'default_revert')->count());
    }

    public function test_defaults_are_per_page_and_unknown_pages_are_not_found(): void
    {
        $this->put('/admin/pages/about', $this->texts(['hero_label' => 'Profil Kami']));
        $this->post('/admin/pages/about/default');

        $this->assertSame(0, PageContentDefault::where('page', 'home')->count());
        $this->get('/admin/pages/home')->assertSee('Halaman ini memakai setelan default bawaan sistem.');

        $this->post('/admin/pages/tidak-ada/default')->assertNotFound();
        $this->delete('/admin/pages/tidak-ada/default')->assertNotFound();
    }

    public function test_preview_and_form_show_the_custom_default_values(): void
    {
        $this->put('/admin/pages/about', $this->texts(['cta_button' => 'Tombol Default']));
        $this->post('/admin/pages/about/default');

        $this->get('/admin/pages/about')->assertSee('value="Tombol Default"', false);
        $this->post('/admin/pages/about/preview/cta', ['content' => []])->assertSee('Tombol Default');
    }

    public function test_all_three_default_actions_ask_for_two_confirmations_naming_the_whole_page(): void
    {
        $this->put('/admin/pages/about', $this->texts(['hero_label' => 'Profil Kami']));
        $this->post('/admin/pages/about/default');

        $html = $this->get('/admin/pages/about')->getContent();

        // Set default baru, kembali ke default, dan pakai default bawaan: semuanya dua langkah.
        $this->assertSame(3, substr_count($html, 'data-confirm-second-title='));
        foreach (['Yakin menyetel default baru?', 'Yakin mereset seluruh halaman?', 'Yakin menghapus default kustom?'] as $title) {
            $this->assertStringContainsString($title, $html);
        }
        // Pesan menyebut seluruh konten halaman yang dikelola di website publik.
        $this->assertGreaterThanOrEqual(3, substr_count(strtolower($html), 'seluruh konten halaman tentang kami') + substr_count($html, 'Seluruh teks, judul, daftar, dan foto halaman Tentang Kami'));

        $js = file_get_contents(resource_path('js/app.js'));
        $this->assertStringContainsString("form.dataset.confirmSecondTitle", $js);
        $this->assertStringContainsString("step: 'Konfirmasi 2 dari 2'", $js);
        $this->assertStringContainsString('Konfirmasi 1 dari 2', $js);
        $this->assertStringContainsString('$store.confirm.step', file_get_contents(resource_path('views/components/admin/confirm-dialog.blade.php')));

        // Halaman biasa (hapus kategori, simpan form) tetap satu konfirmasi.
        $this->get('/admin/categories')->assertDontSee('data-confirm-second-title', false);
    }

    public function test_confirmation_texts_explain_what_each_button_does(): void
    {
        $this->get('/admin/pages/about')
            ->assertSee('Setel default')
            ->assertDontSee('Simpan sebagai default')
            ->assertDontSee('Kembali ke setelan default')
            ->assertSee('data-confirm-title="Setel default?"', false)
            ->assertSee('data-confirm-title="Reset default?"', false)
            ->assertSee('Konten halaman Tentang Kami yang sudah tersimpan akan dijadikan setelan default baru.', false)
            ->assertSee('data-confirm-caution="Setelan default yang lama akan diganti.', false)
            ->assertSee('Seluruh konten halaman Tentang Kami akan dikembalikan ke setelan default (bawaan sistem).', false)
            ->assertSee('data-confirm-caution="Semua perubahan yang sudah tersimpan di halaman ini akan hilang."', false)
            ->assertSee('Setelan default baru mencakup seluruh konten halaman Tentang Kami', false);

        // Setelah ada default kustom, pesan reset menyebut asalnya.
        $this->put('/admin/pages/about', $this->texts(['hero_label' => 'Profil Kami']));
        $this->post('/admin/pages/about/default');

        $this->get('/admin/pages/about')
            ->assertSee('ke setelan default (yang Anda tetapkan lewat &quot;Setel default&quot;).', false)
            // Kedua tombol berukuran sama.
            ->assertSee('w-40', false);
    }

    public function test_first_confirmation_shows_a_caution_box_wired_in_script_and_dialog(): void
    {
        $js = file_get_contents(resource_path('js/app.js'));
        $this->assertStringContainsString('caution: form.dataset.confirmCaution', $js);
        $this->assertStringContainsString("this.caution = caution ?? '';", $js);

        $dialog = file_get_contents(resource_path('views/components/admin/confirm-dialog.blade.php'));
        $this->assertStringContainsString('$store.confirm.caution', $dialog);
        $this->assertStringContainsString('Perhatian:', $dialog);

        // Dua tombol utama sama lebarnya.
        $html = $this->get('/admin/pages/about')->getContent();
        $this->assertSame(2, substr_count($html, 'w-40"'));
    }
}
