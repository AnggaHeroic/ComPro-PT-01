<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\User;
use App\Support\IdleSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class IdleTimeoutTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-22 10:00:00');
        $this->admin = User::factory()->create(['name' => 'Admin Uji', 'password' => bcrypt('rahasia-123')]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** Sesi admin yang aktivitas terakhirnya $minutes menit lalu. */
    private function idleFor(int $minutes): static
    {
        return $this->actingAs($this->admin)->withSession([
            IdleSession::KEY => now()->subMinutes($minutes)->timestamp,
        ]);
    }

    public function test_the_default_limit_is_thirty_minutes_with_a_two_minute_warning(): void
    {
        $this->assertSame(30, config('admin.idle_timeout'));
        $this->assertSame(2, config('admin.idle_warning'));
    }

    public function test_an_admin_idle_for_thirty_minutes_is_logged_out_and_must_log_in_again(): void
    {
        $this->idleFor(31)->get('/admin/categories')->assertRedirect('/login');

        $this->assertGuest();

        // Dicatat sebagai keluar otomatis (satu catatan, bukan juga "Keluar" biasa).
        $log = ActivityLog::latest('id')->first();
        $this->assertSame(['timeout', 'Akun', 'Admin Uji', $this->admin->id], [$log->action, $log->menu, $log->user_name, $log->user_id]);
        $this->assertSame('Keluar otomatis (tidak aktif selama 30 menit)', $log->detail);
        $this->assertSame(['Keluar otomatis', '(tidak aktif selama 30 menit)'], $log->detailLines());
        $this->assertSame('Keluar otomatis', $log->actionLabel());
        $this->assertSame(0, ActivityLog::where('action', 'logout')->count());

        // Pesannya tampil di halaman login, dan halaman admin tidak bisa dibuka lagi tanpa masuk.
        $this->get('/login')->assertSee('keluar otomatis karena tidak aktif selama 30 menit');
        $this->get('/admin')->assertRedirect('/login');

        // Setelah masuk kembali, dashboard bisa dibuka dan masuknya tercatat.
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'rahasia-123'])->assertRedirect('/admin');
        $this->get('/admin')->assertOk();
        $this->assertSame('login', ActivityLog::latest('id')->value('action'));
    }

    public function test_exactly_at_the_limit_logs_out_but_just_under_it_does_not(): void
    {
        $this->idleFor(29)->get('/admin')->assertOk();
        $this->assertAuthenticated();
        $this->assertSame(0, ActivityLog::where('action', 'timeout')->count());

        $this->idleFor(30)->get('/admin')->assertRedirect('/login');
        $this->assertSame(1, ActivityLog::where('action', 'timeout')->count());
    }

    public function test_each_admin_request_restarts_the_countdown(): void
    {
        $this->idleFor(20)->get('/admin')->assertOk();
        $this->assertSame(now()->timestamp, session(IdleSession::KEY));

        // 25 menit setelah permintaan tadi (total 45 dari awal): masih aktif karena hitungan diulang.
        Carbon::setTestNow(now()->addMinutes(25));
        $this->get('/admin/categories')->assertOk();
        $this->assertAuthenticated();
    }

    public function test_the_first_admin_request_after_login_starts_the_clock_without_logging_out(): void
    {
        $this->actingAs($this->admin)->get('/admin')->assertOk();

        $this->assertSame(now()->timestamp, session(IdleSession::KEY));
        $this->assertSame(0, ActivityLog::where('action', 'timeout')->count());
    }

    public function test_ajax_requests_after_the_limit_get_a_401_with_the_login_address(): void
    {
        $this->idleFor(45)
            ->putJson('/admin/categories/reorder', [])
            ->assertStatus(401)
            ->assertJson(['redirect' => route('login')]);

        $this->assertGuest();
        $this->assertSame(1, ActivityLog::where('action', 'timeout')->count());
    }

    public function test_every_admin_area_is_protected_including_the_password_form(): void
    {
        foreach (['/admin', '/admin/categories', '/admin/activities', '/admin/gallery', '/admin/pages', '/admin/pages/about', '/admin/activity-log', '/admin/profil'] as $url) {
            $this->idleFor(40)->get($url)->assertRedirect('/login');
            $this->assertGuest();
        }

        $this->idleFor(40)->put('/password', ['current_password' => 'rahasia-123', 'password' => 'baru-12345678', 'password_confirmation' => 'baru-12345678'])
            ->assertRedirect('/login');
        $this->assertTrue(password_verify('rahasia-123', $this->admin->fresh()->password));
    }

    public function test_keep_alive_refreshes_the_clock_so_a_long_form_session_survives(): void
    {
        $this->idleFor(25)->post('/admin/keep-alive')->assertNoContent();
        $this->assertSame(now()->timestamp, session(IdleSession::KEY));

        Carbon::setTestNow(now()->addMinutes(20));
        $this->get('/admin/pages/about')->assertOk();
        $this->assertAuthenticated();

        // Tanpa ping, sesi yang sama sudah lewat batas.
        $this->idleFor(35)->post('/admin/keep-alive')->assertRedirect('/login');
    }

    public function test_the_idle_logout_endpoint_only_logs_out_when_the_server_agrees(): void
    {
        // Tab lain masih aktif (server baru melihat aktivitas 5 menit lalu): tidak keluar.
        $this->idleFor(5)->postJson('/admin/idle-logout')->assertOk()->assertJson(['active' => true]);
        $this->assertAuthenticated();
        $this->assertSame(0, ActivityLog::where('action', 'timeout')->count());

        // Sudah melewati batas: keluar, tercatat, dan diarahkan ke login.
        $this->idleFor(34)->postJson('/admin/idle-logout')->assertOk()->assertJson(['redirect' => route('login')]);
        $this->assertGuest();
        $log = ActivityLog::latest('id')->first();
        $this->assertSame('timeout', $log->action);
        $this->assertSame(1, ActivityLog::where('action', 'timeout')->count());

        // Pemanggilan oleh tamu ditolak.
        $this->postJson('/admin/idle-logout')->assertUnauthorized();
    }

    public function test_manual_logout_is_still_recorded_as_a_normal_logout(): void
    {
        $this->actingAs($this->admin)->get('/admin')->assertOk();
        $this->post('/logout')->assertRedirect('/');

        $this->assertSame('logout', ActivityLog::latest('id')->value('action'));
        $this->assertSame(0, ActivityLog::where('action', 'timeout')->count());
    }

    public function test_the_dashboard_ships_the_idle_guard_with_the_configured_limits(): void
    {
        $html = $this->actingAs($this->admin)->get('/admin')->assertOk()->getContent();

        $this->assertStringContainsString('idleGuard(', $html);
        $this->assertStringContainsString('timeout: 30', $html);
        $this->assertStringContainsString('warning: 2', $html);
        $this->assertStringContainsString('Tetap masuk', $html);
        $this->assertStringContainsString(route('admin.keep-alive'), str_replace('\\/', '/', $html));
        $this->assertStringContainsString('Sesi akan berakhir', $html);

        // Halaman publik dan login tidak memuatnya.
        auth()->logout();
        $this->get('/')->assertDontSee('idleGuard(', false);
        $this->get('/login')->assertDontSee('idleGuard(', false);

        $js = file_get_contents(resource_path('js/app.js'));
        $this->assertStringContainsString("Alpine.data('idleGuard'", $js);
        $this->assertStringContainsString("'admin-last-activity'", $js);
        // Selama peringatan tampil, aktivitas biasa diabaikan; hanya "Tetap masuk" (touch dipaksa) yang melanjutkan sesi.
        $this->assertStringContainsString('if (this.remaining !== null && !force) return;', $js);
        $this->assertMatchesRegularExpression('/stay\(\) \{\s*this\.setFlag\(false\);\s*this\.touch\(true\);\s*this\.ping\(\);/', $js);
        $this->assertStringContainsString('@click="stay()"', $html);
    }

    public function test_the_log_page_lists_automatic_logouts_with_their_own_label_and_filter(): void
    {
        $this->idleFor(40)->get('/admin')->assertRedirect('/login');
        $this->actingAs($this->admin);

        $this->get('/admin/activity-log?jenis=akses')
            ->assertSee('Keluar otomatis')
            ->assertSee('(tidak aktif selama 30 menit)', false);
        $this->get('/admin/activity-log?action=timeout')->assertSee('(tidak aktif selama 30 menit)', false);
        $this->get('/admin/activity-log?action=logout')->assertDontSee('tidak aktif selama 30 menit');
    }

    public function test_idle_warning_aligns_the_server_clock_and_page_loads_no_longer_restart_it(): void
    {
        // Peringatan dimulai setelah browser menghitung 28 menit tanpa aktivitas.
        $this->idleFor(2)->postJson('/admin/idle-warning', ['idle' => 28 * 60])->assertNoContent();

        $this->assertSame(now()->timestamp - 28 * 60, session(IdleSession::KEY));
        $this->assertTrue((bool) session(IdleSession::WARNING_KEY));

        // Berpindah halaman atau memuat ulang selama peringatan tidak menyegarkan jam sesi.
        foreach (['/admin/categories', '/admin/pages', '/admin'] as $url) {
            $this->get($url)->assertOk();
            $this->assertSame(now()->timestamp - 28 * 60, session(IdleSession::KEY), $url);
        }

        // Dua menit kemudian (total 30), halaman berikutnya mengeluarkan admin dan tercatat.
        Carbon::setTestNow(now()->addMinutes(2));
        $this->get('/admin/categories')->assertRedirect('/login');
        $this->assertGuest();
        $this->assertSame(1, ActivityLog::where('action', 'timeout')->count());
    }

    public function test_stay_logged_in_ends_the_warning_and_restarts_the_clock(): void
    {
        $this->idleFor(2)->postJson('/admin/idle-warning', ['idle' => 28 * 60])->assertNoContent();

        $this->post('/admin/keep-alive')->assertNoContent();

        $this->assertNull(session(IdleSession::WARNING_KEY));
        $this->assertSame(now()->timestamp, session(IdleSession::KEY));

        // Setelah itu halaman kembali menyegarkan jam seperti biasa.
        Carbon::setTestNow(now()->addMinutes(10));
        $this->get('/admin')->assertOk();
        $this->assertSame(now()->timestamp, session(IdleSession::KEY));
    }

    public function test_warning_idle_value_is_clamped_and_requires_a_logged_in_admin(): void
    {
        $this->idleFor(2)->postJson('/admin/idle-warning', ['idle' => 99999])->assertNoContent();
        $this->assertSame(now()->timestamp - 30 * 60, session(IdleSession::KEY));

        $this->idleFor(2)->postJson('/admin/idle-warning', ['idle' => -50])->assertNoContent();
        $this->assertSame(now()->timestamp, session(IdleSession::KEY));

        auth()->logout();
        $this->postJson('/admin/idle-warning', ['idle' => 100])->assertUnauthorized();
    }

    public function test_the_browser_guard_keeps_the_warning_across_pages_and_reloads(): void
    {
        $js = file_get_contents(resource_path('js/app.js'));

        // Memuat halaman tidak mengulang hitungan bila peringatan sedang berjalan.
        $this->assertStringContainsString('if (Date.now() - this.lastActivity() < warningFrom) {', $js);
        $this->assertStringContainsString("warningKey: 'admin-idle-warning'", $js);
        // Peringatan baru memberi tahu server; "Tetap masuk" menghapus penandanya.
        $this->assertStringContainsString('this.markWarning(Math.round(idle / 1000));', $js);
        $this->assertMatchesRegularExpression('/stay\(\) \{\s*this\.setFlag\(false\);\s*this\.touch\(true\);\s*this\.ping\(\);/', $js);

        $html = $this->actingAs($this->admin)->get('/admin')->getContent();
        $this->assertStringContainsString(route('admin.idle-warning'), str_replace('\/', '/', $html));
    }
}
