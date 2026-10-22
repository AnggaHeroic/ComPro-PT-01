<?php

namespace Tests\Feature\Admin;

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
}
