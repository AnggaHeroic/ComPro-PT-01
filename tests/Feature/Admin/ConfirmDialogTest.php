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
            ->assertSee('data-reset-on-cancel', false);

        $this->get('/admin/pages/home')
            ->assertDontSee('onsubmit', false)
            ->assertSee('data-confirm-title="Kembalikan ke bawaan?"', false);
    }

    public function test_confirmation_logic_is_wired_in_the_script(): void
    {
        $js = file_get_contents(resource_path('js/app.js'));

        $this->assertStringContainsString("Alpine.store('confirm'", $js);
        $this->assertMatchesRegularExpression("/document\.addEventListener\(\s*'submit'/", $js);
        $this->assertStringContainsString("method === 'GET'", $js);
        // Urutan hasil seret hanya disimpan setelah dikonfirmasi; bila batal, dikembalikan.
        $this->assertStringContainsString('ok ? this.save(group) : this.restore();', $js);
        $this->assertStringNotContainsString("this.save(row.dataset.group)", $js);
    }
}
