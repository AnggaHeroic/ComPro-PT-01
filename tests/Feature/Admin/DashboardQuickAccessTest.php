<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\User;
use App\Support\PageContents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardQuickAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_dashboard_shows_the_page_content_panel_under_the_summary_cards(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSeeInOrder(['Kategori', 'Aktivitas Usaha', 'Foto Galeri', 'Kelola konten halaman']);
    }

    public function test_dashboard_has_no_quick_access_heading_and_no_other_shortcuts(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertDontSee('Akses cepat')
            ->assertDontSee('Langsung ke bagian yang sering diubah')
            ->assertDontSee('Pintasan lain')
            ->assertDontSee('Draft menunggu')
            ->assertDontSee('catatan hari ini')
            ->assertDontSee('Logo &amp; kontak', false)
            // Judul lama "Konten halaman" diganti menjadi "Kelola konten halaman".
            ->assertSee('Kelola konten halaman');
    }

    public function test_every_page_links_to_its_content_form_with_its_section_count_in_navbar_order(): void
    {
        $response = $this->get('/admin')->assertOk();

        foreach (PageContents::definitions() as $key => $page) {
            $response->assertSee('href="'.route('admin.pages.edit', $key).'"', false)
                ->assertSee($page['label'])
                ->assertSee(count($page['sections']).' bagian dapat diubah');
        }

        $response->assertSeeInOrder(['Beranda', 'Tentang Kami', 'Layanan', 'Kontak Kami']);
    }

    public function test_page_cards_keep_the_original_card_design_and_match_the_summary_cards_spacing(): void
    {
        $html = $this->get('/admin')->getContent();

        // Empat kartu terpisah (bukan satu panel); jarak antarkartu sama dengan tiga kartu ringkasan (gap-5).
        $this->assertSame(4, substr_count($html, 'bagian dapat diubah'));
        $this->assertSame(2, substr_count($html, 'grid gap-5'));
        $this->assertStringNotContainsString('absolute inset-x-0 top-0 h-1 bg-primary', substr($html, strpos($html, 'page-content-title')));

        // Sudut dan bayangan sama seperti kartu di atasnya.
        $cards = substr($html, strpos($html, 'aria-labelledby="page-content-title"'));
        $this->assertStringContainsString('rounded border border-gray-200 bg-white p-4 shadow-sm', $cards);
    }

    public function test_the_recent_changes_panel_is_empty_before_anything_is_changed(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('Terakhir diubah')
            ->assertSee('Belum ada perubahan konten')
            ->assertSee('Lihat semua riwayat');
    }

    public function test_the_recent_changes_panel_lists_the_five_newest_content_changes_and_ignores_logins(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            ActivityLog::forceCreate([
                'user_name' => 'Admin Uji', 'action' => 'updated', 'menu' => 'Kategori',
                'detail' => "Perubahan nomor {$i}", 'created_at' => now()->subMinutes(60 - $i),
            ]);
        }
        // Masuk, keluar, dan ganti password bukan perubahan konten.
        ActivityLog::forceCreate(['user_name' => 'Admin Uji', 'action' => 'login', 'menu' => 'Akun', 'detail' => 'Masuk ke dashboard', 'created_at' => now()]);
        ActivityLog::forceCreate(['user_name' => 'Admin Uji', 'action' => 'password', 'menu' => 'Profil', 'detail' => 'Mengganti password akun', 'created_at' => now()]);

        $html = $this->get('/admin')->assertOk()->getContent();
        $panel = substr($html, strpos($html, 'id="recent-title"'));

        $this->assertSame(5, substr_count($panel, 'Perubahan nomor'));
        // Terbaru di atas; dua yang paling lama tidak tampil.
        $this->assertTrue(strpos($panel, 'Perubahan nomor 7') < strpos($panel, 'Perubahan nomor 3'));
        $this->assertStringNotContainsString('Perubahan nomor 2', $panel);
        $this->assertStringNotContainsString('Perubahan nomor 1<', $panel);
        $this->assertStringNotContainsString('Masuk ke dashboard', $panel);
        $this->assertStringNotContainsString('Mengganti password akun', $panel);
        $this->assertStringContainsString('Admin Uji', $panel);
    }

    public function test_the_recent_changes_panel_shows_who_where_and_when_in_indonesian(): void
    {
        ActivityLog::forceCreate([
            'user_name' => 'Admin Uji', 'action' => 'created', 'menu' => 'Aktivitas Usaha',
            'detail' => 'Menambahkan aktivitas "Contoh Baru" (kategori: Kat, status: Draft)',
            'created_at' => now()->subHours(3),
        ]);

        $this->get('/admin')
            ->assertSee('Menambahkan aktivitas')
            ->assertSee('Admin Uji')
            ->assertSee('Aktivitas Usaha')
            ->assertSee('3 jam yang lalu')
            ->assertSee('href="'.route('admin.activity-log.index').'"', false);
    }

    public function test_the_recent_changes_panel_does_not_change_the_page_card_grid(): void
    {
        $html = $this->get('/admin')->getContent();

        $this->assertSame(2, substr_count($html, 'grid gap-5'));
        $this->assertSame(4, substr_count($html, 'bagian dapat diubah'));
        $this->assertTrue(strpos($html, 'page-content-title') < strpos($html, 'recent-title'));
    }

    public function test_a_visual_banner_sits_between_the_page_cards_and_the_recent_changes(): void
    {
        $html = $this->get('/admin')->assertOk()->assertSee('Semua perubahan tercatat otomatis')->getContent();

        $this->assertTrue(strpos($html, 'page-content-title') < strpos($html, 'data-recent-banner'));
        $this->assertTrue(strpos($html, 'data-recent-banner') < strpos($html, 'recent-title'));
        $this->assertSame(1, substr_count($html, 'data-recent-banner'));
        // Jarak ke bawah lebih lega daripada sebelumnya (mt-8 → mt-10 / mt-12).
        $this->assertStringContainsString('mt-12 overflow-hidden', $html);
        $this->assertStringContainsString('class="mt-10" aria-labelledby="recent-title"', $html);
    }
}
