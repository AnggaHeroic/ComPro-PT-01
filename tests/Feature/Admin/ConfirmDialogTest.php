<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityImage;
use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmDialogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_every_admin_page_ships_the_confirm_dialog_and_enables_it(): void
    {
        $category = ServiceCategory::factory()->create();
        $activity = BusinessActivity::factory()->for($category, 'category')->create();

        foreach ([
            '/admin', '/admin/categories', '/admin/categories/create', "/admin/categories/{$category->id}/edit",
            '/admin/activities', '/admin/activities/create', "/admin/activities/{$activity->id}/edit",
            '/admin/gallery', "/admin/gallery/{$activity->id}", '/admin/pages', '/admin/pages/home', '/admin/pages/about',
            '/admin/profil',
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('<body class="bg-gray-100 text-gray-900" data-admin-confirm>', false)
                ->assertSee('role="alertdialog"', false);
        }
    }

    public function test_logout_skips_confirmation_and_the_login_page_has_no_dialog(): void
    {
        $this->get('/admin')->assertSee('action="'.route('logout').'" class="mt-1" data-no-confirm', false);

        auth()->logout();
        $this->get('/login')->assertDontSee('data-admin-confirm')->assertDontSee('alertdialog');
    }

    public function test_delete_forms_use_the_dialog_instead_of_the_browser_confirm(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Kategori Uji']);
        $activity = BusinessActivity::factory()->for($category, 'category')->create(['name' => 'Aktivitas Uji']);
        $image = ActivityImage::factory()->for($activity, 'activity')->create();

        $this->get('/admin/categories')
            ->assertDontSee('onsubmit', false)
            ->assertSee('data-confirm-title="Hapus kategori?"', false)
            ->assertSee('Kategori &quot;Kategori Uji&quot; akan dihapus permanen', false);

        $this->get('/admin/activities')
            ->assertDontSee('onsubmit', false)
            ->assertSee('data-confirm-title="Hapus aktivitas?"', false);

        $this->get("/admin/gallery/{$activity->id}")
            ->assertDontSee('onsubmit', false)
            ->assertSee('data-confirm-title="Hapus foto?"', false)
            ->assertSee('data-confirm-title="Unggah foto?"', false)
            // Ganti foto tidak lagi mengirim otomatis: file dipilih, lalu admin menekan Simpan.
            ->assertDontSee('data-reset-on-cancel', false)
            ->assertDontSee('@change="$el.form.requestSubmit()"', false)
            ->assertSee('Foto pengganti:', false);

        $this->get('/admin/pages/home')
            ->assertDontSee('onsubmit', false)
            ->assertSee('data-confirm-title="Kembali ke setelan default?"', false);
    }

    public function test_confirmation_logic_is_wired_in_the_script(): void
    {
        $js = file_get_contents(resource_path('js/app.js'));

        $this->assertStringContainsString("Alpine.store('confirm'", $js);
        $this->assertMatchesRegularExpression("/document\.addEventListener\(\s*'submit'/", $js);
        $this->assertStringContainsString("method === 'GET'", $js);
        // Urutan hasil seret hanya ditampung; konfirmasi muncul saat Selesai ditekan.
        $this->assertStringContainsString('this.stage(row.dataset.group)', $js);
        $this->assertStringContainsString('async finish(url)', $js);
        $this->assertStringNotContainsString('this.save(row.dataset.group)', $js);
        $this->assertStringNotContainsString('confirmThenSave', $js);
        $this->assertStringContainsString("secondaryLabel: 'Buang perubahan'", $js);
        $this->assertStringContainsString('async cancel(url)', $js);
        $this->assertStringContainsString('hideCancel: true', $js);
    }

    public function test_reorder_screens_confirm_on_finish_not_while_dragging(): void
    {
        ServiceCategory::factory()->count(2)->create();

        foreach (['/admin/categories?reorder=1', '/admin/activities?reorder=1'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('@click.prevent="finish($el.href)"', false)
                ->assertSee('@click.prevent="cancel($el.href)"', false)
                ->assertSeeInOrder(['Batal', 'Selesai'])
                ->assertSee('Urutan belum disimpan')
                ->assertSee('Perubahan baru disimpan saat Anda menekan Selesai.')
                ->assertDontSee('Perubahan langsung tersimpan');
        }
    }

    public function test_optional_text_fields_are_not_marked_required_in_the_browser(): void
    {
        $html = $this->get('/admin/pages/contact')->getContent();

        // Kata kunci peta opsional: tidak boleh memblokir penyimpanan saat dikosongkan.
        preg_match('/<input[^>]*name="content\[location_map_query\]"[^>]*>/s', $html, $optional);
        $this->assertNotEmpty($optional);
        $this->assertStringNotContainsString('required', $optional[0]);

        preg_match('/<input[^>]*name="content\[contact_email\]"[^>]*>/s', $html, $required);
        $this->assertStringContainsString('required', $required[0]);

        $this->get('/admin/pages/about')->assertSee('Kembali ke setelan default')->assertDontSee('Kembalikan semua teks');
    }
}
