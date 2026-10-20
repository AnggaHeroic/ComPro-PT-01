<?php

namespace Tests\Feature\Admin;

use App\Models\PageContent;
use App\Models\User;
use App\Support\PageContents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageEditorPreviewTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array<string, mixed>> */
    private function texts(string $page, array $overrides = []): array
    {
        $texts = [];
        foreach (PageContents::fields($page) as $key => $field) {
            if (! in_array($field['type'] ?? 'text', ['image'], true)) {
                $texts[$key] = $field['default'];
            }
        }

        return ['content' => array_merge($texts, $overrides)];
    }

    public function test_public_pages_expose_section_markers_only_to_a_logged_in_admin_in_preview_mode(): void
    {
        $expected = [
            '/' => ['hero', 'intro', 'services', 'cta', 'identity'],
            '/tentang-kami' => ['hero', 'profile', 'facts', 'direction', 'objectives', 'cta', 'identity'],
            '/layanan-jasa' => ['hero', 'categories', 'cta', 'identity'],
            '/kontak-kami' => ['hero', 'info', 'location', 'cta', 'identity'],
        ];

        // Tamu: tidak ada penanda dan tidak ada skrip, dengan atau tanpa ?cms-preview.
        foreach (array_keys($expected) as $url) {
            $this->get($url.'?cms-preview=1')->assertOk()
                ->assertDontSee('data-cms-section', false)
                ->assertDontSee('cms-select', false);
        }

        $this->actingAs(User::factory()->create());

        foreach ($expected as $url => $sections) {
            // Tanpa ?cms-preview halaman publik tetap bersih, walau admin sedang login.
            $this->get($url)->assertDontSee('data-cms-section', false);

            $html = $this->get($url.'?cms-preview=1')->assertOk()->getContent();
            $this->assertStringContainsString('cms-select', $html);

            foreach ($sections as $section) {
                $this->assertStringContainsString('data-cms-section="'.$section.'"', $html, "{$url}: {$section}");
            }
        }
    }

    public function test_every_marked_section_exists_in_the_edit_form_of_its_page(): void
    {
        $this->actingAs(User::factory()->create());

        $pages = ['/' => 'home', '/tentang-kami' => 'about', '/layanan-jasa' => 'services', '/kontak-kami' => 'contact'];

        foreach ($pages as $url => $page) {
            $html = $this->get($url.'?cms-preview=1')->getContent();
            preg_match_all('/data-cms-section="([a-z]+)"/', $html, $found);

            foreach (array_unique($found[1]) as $section) {
                if ($section === 'identity') {
                    continue;
                }

                $this->assertArrayHasKey($section, PageContents::definitions()[$page]['sections'], "{$page}: bagian {$section} tidak ada di form");
            }
        }
    }

    public function test_edit_form_has_tabs_per_section_and_a_preview_of_the_public_page(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get('/admin/pages/about')->assertOk();

        foreach (PageContents::definitions()['about']['sections'] as $key => $section) {
            $response->assertSee('id="tab-'.$key.'"', false)->assertSee($section['label']);
        }

        $response->assertSee('x-data="pageEditor(', false)
            ->assertSee('cms-preview=1', false)
            ->assertSee('Pratinjau halaman')
            ->assertSee('Klik bagian untuk mengeditnya')
            ->assertSee('name="bagian"', false)
            // Penghitung karakter dan penanda wajib diisi.
            ->assertSee("x-text=\"n + '/", false);
    }

    public function test_requested_section_is_opened_first_and_saving_returns_to_the_same_section(): void
    {
        $this->actingAs(User::factory()->create());

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
        $this->actingAs(User::factory()->create());

        $this->from('/admin/pages/about?bagian=hero')
            ->followingRedirects()
            ->put('/admin/pages/about', $this->texts('about', ['cta_button' => '']) + ['bagian' => 'hero'])
            ->assertSee("active: 'cta'", false)
            ->assertSee('wajib diisi');
    }

    public function test_pages_list_shows_cards_with_sections_count_and_last_edit(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/pages')
            ->assertOk()
            ->assertSee('Masih memakai teks default')
            ->assertSee('Edit konten')
            ->assertSeeInOrder(['Beranda', 'Tentang Kami', 'Layanan &amp; Jasa', 'Kontak Kami'], false);

        PageContent::create(['page' => 'about', 'key' => 'cta_button', 'value' => 'X']);

        $this->get('/admin/pages')->assertSee('Terakhir diubah');
    }

    public function test_preview_script_blocks_links_and_reports_clicks_to_the_editor(): void
    {
        $this->actingAs(User::factory()->create());

        $html = $this->get('/?cms-preview=1')->getContent();

        $this->assertStringContainsString("window.parent.postMessage({ type: 'cms-select'", $html);
        $this->assertStringContainsString('event.preventDefault();', $html);
        $this->assertStringContainsString("event.data.type !== 'cms-active'", $html);
        // Pesan hanya dipercaya dari asal yang sama.
        $this->assertStringContainsString('event.origin !== origin', $html);
    }
}
