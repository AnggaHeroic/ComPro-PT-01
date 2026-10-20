<?php

namespace Tests\Feature\Admin;

use App\Models\PageContent;
use App\Models\User;
use App\Support\PageContents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AboutPageContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->create());
    }

    /** @return array<string, string> */
    private function texts(array $overrides = []): array
    {
        $texts = [];
        foreach (PageContents::fields('about') as $key => $field) {
            if (($field['type'] ?? 'text') !== 'image') {
                $texts[$key] = $field['default'];
            }
        }

        return ['content' => array_merge($texts, $overrides)];
    }

    public function test_about_page_is_listed_and_shows_defaults(): void
    {
        $this->get('/admin/pages')->assertSee('Tentang Kami');
        $this->get('/admin/pages/about')->assertOk()->assertSee('name="image_hero_image"', false);

        auth()->logout();
        $this->get('/tentang-kami')
            ->assertOk()
            ->assertSee('Mengenal')
            ->assertSee('Established')
            ->assertSee('Misi Utama')
            ->assertSee('Kenali bidang usaha kami.')
            ->assertSee('photo-1497366811353', false);
    }

    public function test_text_changes_appear_on_the_about_page(): void
    {
        $this->put('/admin/pages/about', $this->texts([
            'hero_title_line2' => 'CV. Maju Jaya',
            'facts_1_value' => '2015',
            'mission_2' => 'Misi kedua yang baru.',
        ]))->assertSessionHasNoErrors();

        auth()->logout();
        $this->get('/tentang-kami')->assertSee('CV. Maju Jaya')->assertSee('2015')->assertSee('Misi kedua yang baru.');
    }

    public function test_optional_paragraphs_can_be_hidden_but_required_fields_cannot_be_blank(): void
    {
        $this->put('/admin/pages/about', $this->texts(['profile_paragraph_3' => '', 'profile_paragraph_4' => '']))
            ->assertSessionHasNoErrors();

        auth()->logout();
        $html = $this->get('/tentang-kami')->getContent();
        $this->assertStringNotContainsString('erat neque consequat libero', $html);
        $this->assertStringNotContainsString('vestibulum sapien', $html);
        $this->assertStringContainsString('Integer tincidunt, justo', $html);

        $this->actingAs(User::first());
        $this->put('/admin/pages/about', $this->texts(['profile_paragraph_1' => '']))
            ->assertSessionHasErrors('content.profile_paragraph_1');
    }

    public function test_uploaded_images_are_converted_to_webp_and_shown_on_the_page(): void
    {
        $this->put('/admin/pages/about', $this->texts() + [
            'image_hero_image' => UploadedFile::fake()->image('hero.jpg', 3000, 2000),
        ])->assertSessionHasNoErrors();

        $path = PageContent::where(['page' => 'about', 'key' => 'hero_image'])->value('value');
        $this->assertStringStartsWith('pages/', $path);
        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);

        auth()->logout();
        $this->get('/tentang-kami')
            ->assertSee(Storage::disk('public')->url($path), false)
            ->assertDontSee('photo-1497366811353', false)
            ->assertSee('photo-1497366754035', false);
    }

    public function test_replacing_and_restoring_an_image_removes_the_old_file(): void
    {
        $this->put('/admin/pages/about', $this->texts() + ['image_hero_image' => UploadedFile::fake()->image('a.jpg')]);
        $first = PageContent::where('key', 'hero_image')->value('value');

        $this->put('/admin/pages/about', $this->texts() + ['image_hero_image' => UploadedFile::fake()->image('b.png')]);
        $second = PageContent::where('key', 'hero_image')->value('value');

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        $this->put('/admin/pages/about', $this->texts() + ['remove_image_hero_image' => 1])->assertSessionHasNoErrors();

        $this->assertNull(PageContent::where('key', 'hero_image')->first());
        Storage::disk('public')->assertMissing($second);
        auth()->logout();
        $this->get('/tentang-kami')->assertSee('photo-1497366811353', false);
    }

    public function test_invalid_image_files_are_rejected(): void
    {
        $this->put('/admin/pages/about', $this->texts() + [
            'image_hero_image' => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        ])->assertSessionHasErrors('image_hero_image');

        $this->assertSame(0, PageContent::count());
    }

    public function test_reset_removes_texts_and_uploaded_images(): void
    {
        $this->put('/admin/pages/about', $this->texts(['hero_title_line2' => 'CV. Maju Jaya']) + [
            'image_profile_image' => UploadedFile::fake()->image('p.jpg'),
        ]);
        $path = PageContent::where('key', 'profile_image')->value('value');
        Storage::disk('public')->assertExists($path);

        $this->delete('/admin/pages/about')->assertRedirect('/admin/pages/about');

        $this->assertSame(0, PageContent::count());
        Storage::disk('public')->assertMissing($path);
    }

    public function test_about_markup_does_not_change_when_only_texts_are_edited(): void
    {
        $normalize = fn (string $html) => preg_replace('/\s+/', ' ', preg_replace('/>\s+</', '><', $html));
        auth()->logout();
        $before = $normalize($this->get('/tentang-kami')->getContent());

        $this->actingAs(User::first());
        $this->put('/admin/pages/about', $this->texts(['hero_title_line2' => 'CV. Maju Jaya', 'cta_button' => 'Lihat Semua']));

        auth()->logout();
        $after = $normalize($this->get('/tentang-kami')->getContent());

        $this->assertSame($before, str_replace(['CV. Maju Jaya', 'Lihat Semua'], ['PT. Putra Maju Sukses', 'Lihat Layanan'], $after));
    }
}
