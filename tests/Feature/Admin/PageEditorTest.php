<?php

namespace Tests\Feature\Admin;

use App\Models\PageContent;
use App\Models\User;
use App\Support\PageContents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageEditorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    /** @return array<string, array<string, mixed>> */
    private function texts(string $page, array $overrides = []): array
    {
        $texts = [];
        foreach (PageContents::fields($page) as $key => $field) {
            if (($field['type'] ?? 'text') !== 'image') {
                $texts[$key] = $field['default'];
            }
        }

        return ['content' => array_merge($texts, $overrides)];
    }

    public function test_edit_form_has_numbered_tabs_per_section_and_a_preview_per_section(): void
    {
        $response = $this->get('/admin/pages/about')->assertOk();

        foreach (PageContents::definitions()['about']['sections'] as $key => $section) {
            $response->assertSee('id="tab-'.$key.'"', false)->assertSee($section['label']);
        }

        $response->assertSee('x-data="pageEditor(', false)
            ->assertSee('name="bagian"', false)
            ->assertSee("x-text=\"n + '/", false)
            ->assertSee('Pratinjau di website')
            ->assertSee('Konten bagian ini')
            ->assertSee('Belum disimpan')
            ->assertSee('sectionPreview(', false)
            ->assertDontSee('Pratinjau halaman');
    }

    public function test_public_pages_have_no_preview_markers_or_scripts(): void
    {
        foreach (['/', '/tentang-kami', '/layanan-jasa', '/kontak-kami'] as $url) {
            $this->get($url.'?cms-preview=1')->assertOk()
                ->assertDontSee('data-cms-section', false)
                ->assertDontSee('pointer-events: none', false);
        }
    }

    public function test_requested_section_is_opened_first_and_saving_returns_to_the_same_section(): void
    {
        $this->get('/admin/pages/about?bagian=objectives')->assertSee("active: 'objectives'", false);
        $this->get('/admin/pages/about?bagian=tidak-ada')->assertSee("active: 'hero'", false);

        $this->put('/admin/pages/about', $this->texts('about', ['cta_button' => 'Lihat Semua']) + ['bagian' => 'cta'])
            ->assertRedirect('/admin/pages/about?bagian=cta');

        // Nilai bagian yang tidak dikenal diabaikan.
        $this->put('/admin/pages/about', $this->texts('about') + ['bagian' => '../../x'])
            ->assertRedirect('/admin/pages/about');
    }

    public function test_the_section_with_a_validation_error_is_opened_automatically(): void
    {
        $this->from('/admin/pages/about?bagian=hero')
            ->followingRedirects()
            ->put('/admin/pages/about', $this->texts('about', ['cta_button' => '']) + ['bagian' => 'hero'])
            ->assertSee("active: 'cta'", false)
            ->assertSee('wajib diisi');
    }

    public function test_pages_list_shows_simple_cards_with_a_manage_button_only(): void
    {
        $this->get('/admin/pages')
            ->assertOk()
            ->assertSee('Kelola')
            ->assertSee('5 bagian')
            ->assertSeeInOrder(['Beranda', 'Tentang Kami', 'Layanan &amp; Jasa', 'Kontak Kami'], false)
            ->assertSee('dikelola terpisah melalui menu')
            // Tidak ada tombol lihat, penomoran, atau informasi terakhir diubah.
            ->assertDontSee('Edit konten')
            ->assertDontSee('Terakhir diubah')
            ->assertDontSee('Masih memakai teks default')
            ->assertDontSee('>Lihat</a>', false);

        PageContent::create(['page' => 'about', 'key' => 'cta_button', 'value' => 'X']);

        $this->get('/admin/pages')->assertDontSee('Terakhir diubah');
    }

    public function test_section_preview_renders_the_public_page_with_unsaved_values_and_isolates_one_section(): void
    {
        $response = $this->post('/admin/pages/about/preview/objectives', $this->texts('about', [
            'objectives_title' => 'Judul *belum disimpan*',
            'objective_items' => [['title' => 'Tujuan Sementara', 'text' => 'Uraian sementara.']],
        ]))->assertOk();

        $html = $response->getContent();
        $this->assertStringContainsString('text/html', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('noindex', $response->headers->get('X-Robots-Tag'));

        // Isian yang belum disimpan dipakai.
        $this->assertStringContainsString('<span class="text-primary">belum disimpan</span>', $html);
        $this->assertStringContainsString('Tujuan Sementara', $html);
        $this->assertStringNotContainsString('Memenuhi Kebutuhan', $html);

        // Penanda bagian + skrip yang menyisakan bagian yang diminta saja.
        $this->assertStringContainsString('data-cms-section="objectives"', $html);
        $this->assertStringContainsString('var key = "objectives";', $html);
        $this->assertStringContainsString('pointer-events: none', $html);

        // Tidak ada yang disimpan.
        $this->assertSame(0, PageContent::count());
    }

    public function test_preview_covers_every_section_of_every_page_and_leaves_public_pages_clean(): void
    {
        foreach (PageContents::definitions() as $page => $definition) {
            foreach (array_keys($definition['sections']) as $section) {
                $html = $this->post("/admin/pages/{$page}/preview/{$section}", $this->texts($page))->assertOk()->getContent();

                if ($section !== 'seo') {
                    $this->assertStringContainsString('data-cms-section="'.$section.'"', $html, "{$page}/{$section}");
                }
            }
        }

        // Setelah pratinjau, halaman publik biasa tidak membawa penanda atau nilai sementara.
        auth()->logout();
        foreach (['/', '/tentang-kami', '/layanan-jasa', '/kontak-kami'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('data-cms-section', false);
        }
    }

    public function test_preview_of_contact_values_also_reaches_the_navbar_and_footer(): void
    {
        $html = $this->post('/admin/pages/contact/preview/identity', $this->texts('contact', [
            'contact_phone' => '0361 999 888',
            'footer_description' => 'Deskripsi footer sementara.',
        ]))->getContent();

        $this->assertStringContainsString('data-cms-section="identity"', $html);
        $this->assertStringContainsString('Deskripsi footer sementara.', $html);
        $this->assertStringContainsString('tel:0361999888', $html);
    }

    public function test_preview_ignores_blank_required_values_unknown_sections_and_guests(): void
    {
        // Kolom wajib yang sedang dikosongkan memakai nilai tersimpan, bukan membuat pratinjau kosong.
        $html = $this->post('/admin/pages/about/preview/hero', $this->texts('about', ['hero_title' => '']))->getContent();
        $this->assertStringContainsString('PT. Putra Maju Sukses', $html);

        $this->post('/admin/pages/about/preview/tidak-ada', [])->assertNotFound();
        $this->post('/admin/pages/tidak-ada/preview/hero', [])->assertNotFound();

        auth()->logout();
        $this->post('/admin/pages/about/preview/hero', [])->assertRedirect('/login');
    }
}
