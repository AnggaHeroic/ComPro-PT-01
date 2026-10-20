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

    public function test_edit_form_has_numbered_tabs_per_section_and_no_preview(): void
    {
        $response = $this->get('/admin/pages/about')->assertOk();

        foreach (PageContents::definitions()['about']['sections'] as $key => $section) {
            $response->assertSee('id="tab-'.$key.'"', false)->assertSee($section['label']);
        }

        $response->assertSee('x-data="pageEditor(', false)
            ->assertSee('name="bagian"', false)
            ->assertSee("x-text=\"n + '/", false)
            ->assertDontSee('cms-preview')
            ->assertDontSee('Pratinjau halaman');
    }

    public function test_public_pages_have_no_preview_markers_or_scripts(): void
    {
        foreach (['/', '/tentang-kami', '/layanan-jasa', '/kontak-kami'] as $url) {
            $this->get($url.'?cms-preview=1')->assertOk()
                ->assertDontSee('data-cms-section', false)
                ->assertDontSee('cms-select', false);
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

    public function test_pages_list_shows_cards_with_sections_count_and_last_edit(): void
    {
        $this->get('/admin/pages')
            ->assertOk()
            ->assertSee('Masih memakai teks default')
            ->assertSee('Edit konten')
            ->assertSeeInOrder(['Beranda', 'Tentang Kami', 'Layanan &amp; Jasa', 'Kontak Kami'], false);

        PageContent::create(['page' => 'about', 'key' => 'cta_button', 'value' => 'X']);

        $this->get('/admin/pages')->assertSee('Terakhir diubah');
    }
}
