<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityImage;
use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UnsavedGuardTest extends TestCase
{
    use RefreshDatabase;

    private ServiceCategory $category;

    private BusinessActivity $activity;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $this->category = ServiceCategory::factory()->create(['name' => 'Kat Guard']);
        $this->activity = BusinessActivity::factory()->for($this->category, 'category')->create(['name' => 'Akt Guard']);
    }

    /** Jumlah form berpenjaga pada halaman (atribut data-unsaved-guard di tag form). */
    private function guarded(string $html): int
    {
        return preg_match_all('/<form[^>]*\sdata-unsaved-guard[\s>]/', $html);
    }

    public function test_the_edit_and_create_forms_are_guarded_and_show_the_unsaved_indicator(): void
    {
        foreach ([
            '/admin/categories/create',
            "/admin/categories/{$this->category->id}/edit",
            '/admin/activities/create',
            "/admin/activities/{$this->activity->id}/edit",
            '/admin/pages/about',
        ] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertSame(1, $this->guarded($html), $url);
            $this->assertStringContainsString('Ada perubahan yang belum disimpan', $html, $url);
            $this->assertStringContainsString('$store.unsaved.dirty', $html, $url);
        }
    }

    public function test_the_profile_forms_and_the_gallery_forms_are_guarded(): void
    {
        $this->assertSame(2, $this->guarded($this->get('/admin/profil')->assertOk()->getContent()));

        ActivityImage::create(['business_activity_id' => $this->activity->id, 'image_path' => 'galleries/x.webp', 'sort_order' => 1]);
        ActivityImage::create(['business_activity_id' => $this->activity->id, 'image_path' => 'galleries/y.webp', 'sort_order' => 2]);

        // Satu form unggah + satu form ubah per foto; form urutan punya penanda sendiri dan tidak dijaga.
        $this->assertSame(3, $this->guarded($this->get("/admin/gallery/{$this->activity->id}")->assertOk()->getContent()));
    }

    public function test_lists_search_forms_and_delete_forms_are_never_guarded(): void
    {
        foreach (['/admin/categories', '/admin/activities', '/admin/gallery', '/admin/activity-log', '/admin/pages'] as $url) {
            $this->assertSame(0, $this->guarded($this->get($url)->assertOk()->getContent()), $url);
        }
    }

    public function test_the_active_section_marker_in_the_page_editor_is_ignored_when_comparing(): void
    {
        $html = $this->get('/admin/pages/about')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<input type="hidden" name="bagian"[^>]*data-unsaved-ignore/', $html);
    }

    public function test_a_form_reloaded_after_validation_errors_starts_out_marked_as_unsaved(): void
    {
        // Normal: tidak ditandai.
        $this->assertStringNotContainsString('data-unsaved-dirty', $this->get("/admin/categories/{$this->category->id}/edit")->getContent());

        $html = $this->from("/admin/categories/{$this->category->id}/edit")
            ->followingRedirects()
            ->put("/admin/categories/{$this->category->id}", ['name' => '', 'slug' => $this->category->slug])
            ->getContent();

        $this->assertMatchesRegularExpression('/<form[^>]*data-unsaved-guard[^>]*data-unsaved-dirty/', $html);
        $this->assertSame(1, $this->guarded($html));
    }

    public function test_the_guard_script_is_loaded_and_covers_links_unload_and_automatic_logout(): void
    {
        $guard = file_get_contents(resource_path('js/unsaved-guard.js'));
        $app = file_get_contents(resource_path('js/app.js'));

        $this->assertStringContainsString("import { setupUnsavedGuard } from './unsaved-guard'", $app);
        $this->assertStringContainsString('setupUnsavedGuard(Alpine)', $app);

        // Dialog tautan, peringatan bawaan browser, dan pengecualian keluar otomatis.
        $this->assertStringContainsString('Perubahan belum disimpan', $guard);
        $this->assertStringContainsString('Tetap di halaman', $guard);
        $this->assertStringContainsString('Keluar tanpa menyimpan', $guard);
        $this->assertStringContainsString("'beforeunload'", $guard);
        $this->assertStringContainsString("'admin:allow-leave'", $guard);
        $this->assertSame(3, substr_count($app, "dispatchEvent(new Event('admin:allow-leave'))"));

        // Pemasangan setelah pendengar konfirmasi simpan, agar hanya pengiriman yang dilanjutkan yang dianggap "menyimpan".
        $this->assertTrue(strpos($app, "'submit',\n") < strpos($app, 'setupUnsavedGuard(Alpine)') || strpos($app, "'submit',\r\n") < strpos($app, 'setupUnsavedGuard(Alpine)'));
    }
}
