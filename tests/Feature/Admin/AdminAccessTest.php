<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_admin_routes(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/profil')->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_open_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_authenticated_user_visiting_login_is_redirected(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/login')
            ->assertRedirect();
    }

    public function test_public_registration_and_password_reset_are_not_available(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
        $this->get('/forgot-password')->assertNotFound();
    }

    public function test_admin_shortcut_adds_only_its_own_markup_and_does_not_touch_the_menu(): void
    {
        $normalize = fn (string $html) => preg_replace('/\s+/', ' ', preg_replace('/>\s+</', '><', $html));

        foreach (['/', '/layanan-jasa', '/kontak-kami'] as $url) {
            $guest = $normalize($this->get($url)->getContent());

            $this->actingAs(User::factory()->create());
            $admin = $normalize($this->get($url)->getContent());
            auth()->logout();

            // Buang hanya <a> tombol dashboard di desktop (header) dan menu mobile; sisanya harus sama.
            $stripped = preg_replace('#<a href="[^"]*/admin"[^>]*>.*?</a>#', '', $admin);
            $stripped = str_replace('<nav class="container-site relative"', '<nav class="container-site"', $stripped);

            $this->assertSame($guest, $stripped, "Markup selain tombol admin berubah di {$url}");
        }
    }

    public function test_mobile_sidebar_starts_hidden_so_it_does_not_flash_and_slide_closed_on_page_load(): void
    {
        $this->actingAs(User::factory()->create());

        $html = $this->get('/admin/categories')->assertOk()->getContent();

        $start = strpos($html, '<aside');
        $this->assertNotFalse($start, 'Sidebar tidak ditemukan.');
        $aside = substr($html, $start, strpos($html, '</nav>', $start) - $start);

        // Penanda "pending" dipasang sejak server dan dilepas Alpine setelah siap.
        $this->assertStringContainsString('data-pending', $aside);
        $this->assertStringContainsString("removeAttribute('data-pending')", $aside);
        // Animasi geser tetap ada untuk buka/tutup lewat tombol menu.
        $this->assertStringContainsString('transition-transform', $aside);

        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertMatchesRegularExpression('/max-width:\s*1023\.98px\)\s*\{\s*aside\[data-pending\]\s*\{\s*translate:\s*-100% 0;\s*transition:\s*none;/', $css);
    }

    public function test_admin_sticky_header_layers_do_not_stack_on_mobile(): void
    {
        $this->actingAs(User::factory()->create());

        $html = $this->get('/admin')->assertOk()->getContent();

        // Bar logo + hamburger: satu-satunya elemen sticky di mobile.
        $this->assertStringContainsString('sticky top-0 z-30 flex h-14', $html);
        // Header halaman tidak lagi sticky dengan offset tetap di mobile; hanya sticky mulai desktop.
        $this->assertStringNotContainsString('sticky top-14', $html);
        $this->assertStringContainsString('lg:sticky lg:top-0 lg:z-20 lg:h-16', $html);
        $this->assertSame(1, preg_match_all('/(?<![:\w-])sticky(?![\w-])/', $html), 'Hanya satu elemen yang sticky tanpa awalan breakpoint.');
    }

    public function test_admin_dashboard_link_is_only_visible_to_logged_in_admin_on_public_pages(): void
    {
        $dashboardUrl = route('admin.dashboard');

        foreach (['/', '/tentang-kami', '/layanan-jasa', '/kontak-kami'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee('Dashboard Admin')
                ->assertDontSee($dashboardUrl, false);
        }

        $this->actingAs(User::factory()->create());

        foreach (['/', '/tentang-kami', '/layanan-jasa', '/kontak-kami'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Dashboard Admin')
                ->assertSee($dashboardUrl, false);
        }
    }
}
