<?php

namespace Tests\Feature\Catalog;

use App\Models\ActivityImage;
use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_public_routes_render_with_seeded_data(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $urls = ['/', '/tentang-kami', '/layanan-jasa', '/kontak-kami'];

        foreach (ServiceCategory::with('activities')->get() as $category) {
            $urls[] = route('services.category', $category->slug, false);

            foreach ($category->activities as $activity) {
                $urls[] = route('services.detail', [$category->slug, $activity->slug], false);
            }
        }

        $this->assertCount(4 + 5 + 14, $urls);

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_index_lists_categories_in_sort_order_from_database(): void
    {
        ServiceCategory::factory()->create(['name' => 'Kedua', 'slug' => 'kedua', 'sort_order' => 2]);
        ServiceCategory::factory()->create(['name' => 'Pertama', 'slug' => 'pertama', 'sort_order' => 1]);

        $this->get('/layanan-jasa')
            ->assertOk()
            ->assertSeeInOrder(['Pertama', 'Kedua']);
    }

    public function test_navbar_and_footer_reflect_database_categories(): void
    {
        ServiceCategory::factory()->create(['name' => 'Kategori Baru Admin', 'slug' => 'kategori-baru-admin']);

        $this->get('/tentang-kami')
            ->assertOk()
            ->assertSee('Kategori Baru Admin')
            ->assertSee('/layanan-jasa/kategori-baru-admin', false);
    }

    public function test_home_service_cards_come_from_database_in_order_with_anchor_links(): void
    {
        ServiceCategory::factory()->create(['name' => 'Kartu Kedua', 'slug' => 'kartu-kedua', 'description' => 'Deskripsi kedua.', 'sort_order' => 2]);
        ServiceCategory::factory()->create(['name' => 'Kartu Pertama', 'slug' => 'kartu-pertama', 'description' => 'Deskripsi pertama.', 'sort_order' => 1]);
        ServiceCategory::factory()->create(['name' => 'Kartu Draft', 'slug' => 'kartu-draft', 'is_published' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Kartu Pertama', 'Deskripsi pertama.', 'Kartu Kedua', 'Deskripsi kedua.'])
            ->assertSee('/layanan-jasa/kartu-pertama#kartu-pertama', false)
            ->assertDontSee('Kartu Draft');
    }

    public function test_home_service_cards_have_all_five_seeded_categories(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('/layanan-jasa/construction-supplies#construction-supplies', false)
            ->assertSee('/layanan-jasa/technology-digital#technology-digital', false);
    }

    public function test_category_page_shows_only_its_own_published_activities(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'kat-a']);
        $other = ServiceCategory::factory()->create(['slug' => 'kat-b']);

        BusinessActivity::factory()->for($category, 'category')->create(['name' => 'Aktivitas Tayang', 'slug' => 'tayang']);
        BusinessActivity::factory()->for($category, 'category')->create(['name' => 'Aktivitas Draft', 'slug' => 'draft', 'is_published' => false]);
        BusinessActivity::factory()->for($other, 'category')->create(['name' => 'Aktivitas Milik B', 'slug' => 'milik-b']);

        $this->get('/layanan-jasa/kat-a')
            ->assertOk()
            ->assertSee('Aktivitas Tayang')
            ->assertDontSee('Aktivitas Draft')
            ->assertDontSee('Aktivitas Milik B');
    }

    public function test_invalid_slugs_return_404(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'kat-a']);
        $other = ServiceCategory::factory()->create(['slug' => 'kat-b']);
        BusinessActivity::factory()->for($other, 'category')->create(['slug' => 'milik-b']);

        $this->get('/layanan-jasa/tidak-ada')->assertNotFound();
        $this->get('/layanan-jasa/kat-a/tidak-ada')->assertNotFound();
        // Aktivitas ada, tetapi bukan milik kategori pada URL.
        $this->get('/layanan-jasa/'.$category->slug.'/milik-b')->assertNotFound();
    }

    public function test_unpublished_category_and_activity_return_404(): void
    {
        $hidden = ServiceCategory::factory()->create(['slug' => 'tersembunyi', 'is_published' => false]);
        BusinessActivity::factory()->for($hidden, 'category')->create(['slug' => 'a']);

        $visible = ServiceCategory::factory()->create(['slug' => 'tampil']);
        BusinessActivity::factory()->for($visible, 'category')->create(['slug' => 'draft', 'is_published' => false]);

        $this->get('/layanan-jasa/tersembunyi')->assertNotFound();
        $this->get('/layanan-jasa/tersembunyi/a')->assertNotFound();
        $this->get('/layanan-jasa/tampil/draft')->assertNotFound();
        $this->get('/layanan-jasa')->assertDontSee('tersembunyi');
    }

    public function test_detail_page_without_gallery_renders_without_error(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'kat']);
        BusinessActivity::factory()->for($category, 'category')->create(['slug' => 'tanpa-galeri']);

        $this->get('/layanan-jasa/kat/tanpa-galeri')->assertOk();
    }

    public function test_detail_page_passes_gallery_in_order_to_lightbox(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'kat']);
        $activity = BusinessActivity::factory()->for($category, 'category')->create(['slug' => 'dengan-galeri']);

        ActivityImage::factory()->for($activity, 'activity')->create(['image_path' => 'https://example.com/dua.jpg', 'sort_order' => 2]);
        ActivityImage::factory()->for($activity, 'activity')->create(['image_path' => 'https://example.com/satu.jpg', 'sort_order' => 1]);

        $this->get('/layanan-jasa/kat/dengan-galeri')
            ->assertOk()
            ->assertSeeInOrder(['https://example.com/satu.jpg', 'https://example.com/dua.jpg'], false);
    }

    public function test_pages_do_not_run_queries_per_activity(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'kat']);
        BusinessActivity::factory()->count(8)->for($category, 'category')->create();

        $queries = 0;
        \DB::listen(function () use (&$queries) {
            $queries++;
        });

        $this->get('/layanan-jasa/kat')->assertOk();

        $this->assertLessThanOrEqual(6, $queries);
    }
}
