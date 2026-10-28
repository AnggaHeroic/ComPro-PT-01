<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogGroupsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['name' => 'Admin Uji']));

        $this->log('created', 'Kategori', 'Menambahkan kategori "Isi Konten Satu" (status: Terbit)', '2026-10-10 09:00:00');
        $this->log('updated', 'Galeri', 'Mengubah foto di galeri aktivitas "Isi Konten Dua"', '2026-10-11 09:00:00');
        $this->log('login', 'Akun', 'Masuk ke dashboard', '2026-10-10 08:00:00');
        $this->log('logout', 'Akun', 'Keluar dari dashboard', '2026-10-10 18:00:00');
        $this->log('timeout', 'Akun', 'Keluar otomatis (tidak aktif selama 30 menit)', '2026-10-11 20:00:00');
        $this->log('profile', 'Profil', 'Mengubah informasi profil (yang diubah: Nama)', '2026-10-12 11:00:00');
        $this->log('password', 'Profil', 'Mengganti password akun', '2026-10-12 10:00:00');
    }

    private function log(string $action, string $menu, string $detail, string $at): void
    {
        $log = new ActivityLog(['user_name' => 'Admin Uji', 'action' => $action, 'menu' => $menu, 'detail' => $detail]);
        $log->created_at = $at;
        $log->save();
    }

    public function test_the_default_tab_shows_everything(): void
    {
        $this->get('/admin/activity-log')
            ->assertOk()
            ->assertSee('Isi Konten Satu')->assertSee('Isi Konten Dua')
            ->assertSee('Masuk ke dashboard')->assertSee('Keluar dari dashboard')
            ->assertSee('tidak aktif selama 30 menit')->assertSee('Mengganti password akun')
            ->assertSee('7 catatan');
    }

    public function test_the_content_tab_shows_content_changes_only(): void
    {
        $this->get('/admin/activity-log?jenis=konten')
            ->assertOk()
            ->assertSee('Isi Konten Satu')->assertSee('Isi Konten Dua')
            ->assertDontSee('Masuk ke dashboard')->assertDontSee('Keluar dari dashboard')
            ->assertDontSee('tidak aktif selama 30 menit')->assertDontSee('Mengganti password akun')
            ->assertSee('2 catatan');
    }

    public function test_the_access_tab_shows_logins_logouts_timeouts_and_password_changes(): void
    {
        $this->get('/admin/activity-log?jenis=akses')
            ->assertOk()
            ->assertSee('Masuk ke dashboard')->assertSee('Keluar dari dashboard')
            ->assertSee('tidak aktif selama 30 menit')->assertSee('Mengganti password akun')
            ->assertDontSee('Isi Konten Satu')->assertDontSee('Isi Konten Dua')
            ->assertSee('5 catatan');
    }

    public function test_the_all_tab_shows_everything(): void
    {
        $this->get('/admin/activity-log?jenis=semua')
            ->assertOk()
            ->assertSee('Isi Konten Satu')->assertSee('Masuk ke dashboard')->assertSee('Mengganti password akun')
            ->assertSee('7 catatan');
    }

    public function test_an_unknown_tab_falls_back_to_the_default_tab_which_shows_everything(): void
    {
        $this->get('/admin/activity-log?jenis=sembarang')->assertOk()->assertSee('Isi Konten Satu')->assertSee('Masuk ke dashboard')->assertSee('7 catatan');
    }

    public function test_the_tabs_show_counts_that_follow_the_search_and_the_date_range(): void
    {
        $html = $this->get('/admin/activity-log')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#Perubahan konten\s*<span[^>]*>2</span>#', $html);
        $this->assertMatchesRegularExpression('#Akses akun\s*<span[^>]*>5</span>#', $html);
        $this->assertMatchesRegularExpression('#Semua\s*<span[^>]*>7</span>#', $html);

        // Hitungan di tab ikut tanggal: hanya 10 Oktober.
        $html = $this->get('/admin/activity-log?dari=2026-10-10&sampai=2026-10-10')->getContent();
        $this->assertMatchesRegularExpression('#Perubahan konten\s*<span[^>]*>1</span>#', $html);
        $this->assertMatchesRegularExpression('#Akses akun\s*<span[^>]*>2</span>#', $html);

        // ... dan ikut pencarian.
        $html = $this->get('/admin/activity-log?q=Masuk')->getContent();
        $this->assertMatchesRegularExpression('#Perubahan konten\s*<span[^>]*>0</span>#', $html);
        $this->assertMatchesRegularExpression('#Akses akun\s*<span[^>]*>1</span>#', $html);
    }

    public function test_tab_links_keep_the_search_and_dates_but_drop_menu_and_action(): void
    {
        $html = $this->get('/admin/activity-log?q=dashboard&dari=2026-10-01&sampai=2026-10-31&menu=Galeri&action=updated')->getContent();

        $this->assertStringContainsString('href="'.e(route('admin.activity-log.index', ['jenis' => 'akses', 'q' => 'dashboard', 'dari' => '2026-10-01', 'sampai' => '2026-10-31'])).'"', $html);
        // Tab awal ("Semua") tidak memakai parameter jenis.
        $this->assertStringContainsString('href="'.e(route('admin.activity-log.index', ['q' => 'dashboard', 'dari' => '2026-10-01', 'sampai' => '2026-10-31'])).'"', $html);
        $this->assertStringNotContainsString('jenis=akses&amp;q=dashboard&amp;dari=2026-10-01&amp;sampai=2026-10-31&amp;menu', $html);
    }

    public function test_the_filters_follow_the_open_tab(): void
    {
        // Perubahan konten: menu konten saja, aksi konten saja.
        $content = $this->get('/admin/activity-log?jenis=konten')->getContent();
        $this->assertStringContainsString('<option value="Kategori"', $content);
        $this->assertStringNotContainsString('<option value="Akun"', $content);
        $this->assertStringNotContainsString('<option value="login"', $content);
        $this->assertStringContainsString('<option value="updated"', $content);

        // Akses akun: tanpa penyaring menu dan tanpa kolom Menu; aksinya hanya masuk/keluar/dst.
        $access = $this->get('/admin/activity-log?jenis=akses')->getContent();
        $this->assertStringNotContainsString('aria-label="Filter menu"', $access);
        $this->assertStringNotContainsString('>Menu</th>', $access);
        $this->assertStringContainsString('<option value="login"', $access);
        $this->assertStringContainsString('<option value="timeout"', $access);
        $this->assertStringNotContainsString('<option value="created"', $access);

        // Semua: seperti sebelumnya.
        $all = $this->get('/admin/activity-log?jenis=semua')->getContent();
        $this->assertStringContainsString('aria-label="Filter menu"', $all);
        $this->assertStringContainsString('>Menu</th>', $all);
        $this->assertStringContainsString('<option value="login"', $all);
        $this->assertStringContainsString('<option value="created"', $all);
    }

    public function test_a_filter_from_another_tab_is_ignored(): void
    {
        // Aksi "login" bukan bagian dari perubahan konten: diabaikan, jenis dipertahankan.
        $this->get('/admin/activity-log?jenis=konten&action=login')->assertSee('Isi Konten Satu')->assertDontSee('Masuk ke dashboard');
        // Menu "Akun" tidak ada di perubahan konten.
        $this->get('/admin/activity-log?jenis=konten&menu=Akun')->assertSee('2 catatan');
        // Penyaring menu tidak berlaku di akses akun.
        $this->get('/admin/activity-log?jenis=akses&menu=Kategori')->assertSee('5 catatan');
    }

    public function test_old_links_with_an_action_or_menu_open_the_tab_that_holds_it(): void
    {
        $this->get('/admin/activity-log?action=timeout')->assertSee('tidak aktif selama 30 menit')->assertDontSee('Isi Konten');
        $this->get('/admin/activity-log?action=password')->assertSee('Mengganti password akun');
        $this->get('/admin/activity-log?menu=Akun')->assertSee('Masuk ke dashboard')->assertDontSee('Isi Konten');
        $this->get('/admin/activity-log?menu=Galeri')->assertSee('Isi Konten Dua')->assertDontSee('Masuk ke dashboard');
    }

    public function test_reset_keeps_the_open_tab(): void
    {
        $html = $this->get('/admin/activity-log?jenis=akses&q=Masuk')->getContent();
        $this->assertStringContainsString('href="'.route('admin.activity-log.index', ['jenis' => 'akses']).'"', $html);
        $this->assertStringContainsString('>Reset</a>', $html);

        $this->assertStringContainsString('<input type="hidden" name="jenis" value="akses">', $html);
        $this->assertStringNotContainsString('name="jenis"', $this->get('/admin/activity-log')->getContent());
    }

    public function test_the_tabs_are_ordered_all_then_access_then_content_changes_from_left_to_right(): void
    {
        $this->assertSame(['semua', 'akses', 'konten'], array_keys(ActivityLog::groups()));

        $html = $this->get('/admin/activity-log')->assertOk()->getContent();
        $nav = substr($html, strpos($html, 'aria-label="Jenis catatan"'));

        $this->assertTrue(strpos($nav, 'Semua') < strpos($nav, 'Akses akun'));
        $this->assertTrue(strpos($nav, 'Akses akun') < strpos($nav, 'Perubahan konten'));

        // Tab yang terbuka saat halaman dibuka tanpa pilihan adalah "Semua" (paling kiri).
        $this->assertSame('semua', ActivityLog::DEFAULT_GROUP);
        $this->assertMatchesRegularExpression('#aria-current="page"[^>]*>\s*Semua#', $html);
        $this->assertDoesNotMatchRegularExpression('#aria-current="page"[^>]*>\s*Perubahan konten#', $html);
    }

    public function test_the_groups_cover_every_action_exactly_once(): void
    {
        $groups = ActivityLog::groups();
        $covered = array_merge($groups['konten']['actions'], $groups['akses']['actions']);

        $this->assertEqualsCanonicalizing(array_keys(ActivityLog::actions()), $covered);
        $this->assertSame($covered, array_unique($covered));
    }

    public function test_pagination_links_keep_the_open_tab(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            $this->log('updated', 'Kategori', "Perubahan banyak {$i}", '2026-10-13 10:00:00');
        }

        $this->get('/admin/activity-log')->assertOk()->assertSee('page=2');

        for ($i = 1; $i <= 30; $i++) {
            $this->log('login', 'Akun', "Masuk banyak {$i}", '2026-10-14 10:00:00');
        }

        $html = $this->get('/admin/activity-log?jenis=akses')->getContent();
        $this->assertStringContainsString('jenis=akses&amp;page=2', $html);
    }
}
