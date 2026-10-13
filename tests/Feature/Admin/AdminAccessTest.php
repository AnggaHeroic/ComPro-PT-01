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
