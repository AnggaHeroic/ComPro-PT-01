<?php

namespace Tests\Feature\Admin;

use App\Models\BusinessActivity;
use App\Models\PageContent;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Support\PageContents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicesPageContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    /** @return array<string, array<string, string>> */
    private function texts(array $overrides = []): array
    {
        $texts = [];
        foreach (PageContents::fields('services') as $key => $field) {
            $texts[$key] = $field['default'];
        }

        return ['content' => array_merge($texts, $overrides)];
    }

    public function test_services_page_is_listed_and_defaults_are_shown_publicly(): void
    {
        $this->get('/admin/pages')->assertSee('Layanan &amp; Jasa', false);
        $this->get('/admin/pages/services')
            ->assertOk()
            ->assertSee('name="content[hero_title]"', false)
            ->assertSee('name="content[categories_card_link]"', false)
            ->assertSee('dikelola terpisah');

        ServiceCategory::factory()->create(['name' => 'Kategori Satu', 'is_published' => true]);

        auth()->logout();
        $this->get('/layanan-jasa')
            ->assertOk()
            ->assertSee('Beragam bidang usaha.')
            ->assertSee('Satu cakupan layanan.')
            ->assertSee('Pilih bidang yang ingin Anda lihat.')
            ->assertSee('Kategori Satu')
            ->assertSee('Lihat layanan')
            ->assertSee('Kenali layanan kami');
    }

    public function test_texts_titles_and_card_link_are_editable_without_touching_the_category_list(): void
    {
        $normalize = fn (string $html) => preg_replace('/\s+/', ' ', preg_replace('/>\s+</', '><', $html));

        $category = ServiceCategory::factory()->create(['name' => 'Kategori Dua', 'is_published' => true]);
        BusinessActivity::factory()->for($category, 'category')->create();
        ServiceCategory::factory()->create(['name' => 'Draft Tersembunyi', 'is_published' => false, 'slug' => null]);

        auth()->logout();
        $before = $normalize($this->get('/layanan-jasa')->getContent());

        $this->actingAs(User::first());
        $this->put('/admin/pages/services', $this->texts([
            'hero_title' => "Semua bidang.\n*Satu perusahaan.*",
            'categories_card_link' => 'Selengkapnya',
            'cta_title' => 'Ingin tahu *lebih banyak?*',
        ]))->assertSessionHasNoErrors();

        auth()->logout();
        $html = $this->get('/layanan-jasa')->getContent();

        $this->assertStringContainsString('Semua bidang.<br>', $html);
        $this->assertStringContainsString('<span class="text-primary">Satu perusahaan.</span>', $html);
        $this->assertStringContainsString('<span class="text-primary">lebih banyak?</span>', $html);
        $this->assertStringContainsString('Selengkapnya', $html);
        // Daftar kategori tetap berasal dari menu Kategori: yang draft tidak tampil.
        $this->assertStringContainsString('Kategori Dua', $html);
        $this->assertStringNotContainsString('Draft Tersembunyi', $html);

        $restored = str_replace('Selengkapnya', 'Lihat layanan', $normalize($html));
        $restored = str_replace(
            ['Semua bidang.<br><span class="text-primary">Satu perusahaan.</span>', 'Ingin tahu <span class="text-primary">lebih banyak?</span>'],
            ['Beragam bidang usaha.<br><span class="text-primary">Satu cakupan layanan.</span>', 'Kenali layanan kami <span class="text-primary">lebih lanjut.</span>'],
            $restored,
        );
        $this->assertSame($before, $restored);
    }

    public function test_validation_and_reset(): void
    {
        $this->put('/admin/pages/services', $this->texts(['hero_title' => 'Judul *rusak']))
            ->assertSessionHasErrors('content.hero_title');
        $this->put('/admin/pages/services', $this->texts(['hero_description' => '']))
            ->assertSessionHasErrors('content.hero_description');
        $this->assertSame(0, PageContent::count());

        $this->put('/admin/pages/services', $this->texts(['categories_title' => 'Judul Baru']));
        $this->assertSame(1, PageContent::count());

        $this->delete('/admin/pages/services')->assertRedirect('/admin/pages/services');
        $this->assertSame(0, PageContent::count());
    }

    public function test_guests_cannot_edit_and_the_edit_page_links_to_the_public_page(): void
    {
        $this->get('/admin/pages/services')->assertSee(route('services'), false);

        auth()->logout();
        $this->get('/admin/pages/services')->assertRedirect('/login');
        $this->put('/admin/pages/services', $this->texts())->assertRedirect('/login');
    }

    public function test_pages_are_listed_in_the_same_order_as_the_navbar(): void
    {
        $this->assertSame(['home', 'about', 'services', 'contact'], array_keys(PageContents::definitions()));

        $this->get('/admin/pages')->assertSeeInOrder(['Beranda', 'Tentang Kami', 'Layanan &amp; Jasa', 'Kontak Kami'], false);
    }
}
