<?php

namespace Tests\Feature\Catalog;

use App\Models\ActivityImage;
use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_five_categories_and_fourteen_activities_with_original_slugs(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $this->assertSame(5, ServiceCategory::count());
        $this->assertSame(14, BusinessActivity::count());

        $this->assertSame(
            ['technology-digital', 'home-lifestyle', 'sports-music', 'industrial-machinery', 'construction-supplies'],
            ServiceCategory::ordered()->pluck('slug')->all(),
        );

        $this->assertSame([4, 3, 2, 3, 2], ServiceCategory::ordered()->withCount('activities')->pluck('activities_count')->all());
        $this->assertSame(0, BusinessActivity::whereNotNull('kbli_code')->count());
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);

        $this->assertSame(5, ServiceCategory::count());
        $this->assertSame(14, BusinessActivity::count());
        $this->assertSame(8, ActivityImage::count());
    }

    public function test_reseeding_does_not_overwrite_admin_changes(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $activity = BusinessActivity::where('slug', 'perdagangan-komputer-dan-perlengkapan')->firstOrFail();
        $activity->update(['name' => 'Diubah admin', 'image_path' => 'activities/baru.jpg']);
        $activity->images()->delete();
        ServiceCategory::where('slug', 'sports-music')->update(['description' => 'Deskripsi baru']);

        $this->seed(ServiceCatalogSeeder::class);

        $activity->refresh();
        $this->assertSame('Diubah admin', $activity->name);
        $this->assertSame('activities/baru.jpg', $activity->image_path);
        $this->assertSame(0, $activity->images()->count());
        $this->assertSame('Deskripsi baru', ServiceCategory::where('slug', 'sports-music')->value('description'));
    }

    public function test_only_one_activity_has_a_gallery_and_the_rest_are_empty(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $activity = BusinessActivity::where('slug', 'perdagangan-komputer-dan-perlengkapan')->firstOrFail();

        $this->assertCount(8, $activity->images);
        $this->assertSame(range(1, 8), $activity->images->pluck('sort_order')->all());
        $this->assertSame(1, BusinessActivity::has('images')->count());
        $this->assertTrue(BusinessActivity::where('slug', 'alat-musik')->firstOrFail()->images->isEmpty());
    }

    public function test_activity_slug_is_unique_per_category_only(): void
    {
        $first = ServiceCategory::factory()->create();
        $second = ServiceCategory::factory()->create();

        BusinessActivity::factory()->for($first, 'category')->create(['slug' => 'sama']);
        BusinessActivity::factory()->for($second, 'category')->create(['slug' => 'sama']);

        $this->assertSame(2, BusinessActivity::where('slug', 'sama')->count());

        $this->expectException(QueryException::class);
        BusinessActivity::factory()->for($first, 'category')->create(['slug' => 'sama']);
    }

    public function test_category_slug_must_be_unique(): void
    {
        ServiceCategory::factory()->create(['slug' => 'unik']);

        $this->expectException(QueryException::class);
        ServiceCategory::factory()->create(['slug' => 'unik']);
    }

    public function test_category_with_activities_cannot_be_deleted(): void
    {
        $activity = BusinessActivity::factory()->create();

        $this->expectException(QueryException::class);
        $activity->category->delete();
    }

    public function test_deleting_an_activity_removes_its_gallery_rows(): void
    {
        $image = ActivityImage::factory()->create();

        $image->activity->delete();

        $this->assertSame(0, ActivityImage::count());
    }

    public function test_relations_and_ordering_are_deterministic(): void
    {
        $category = ServiceCategory::factory()->create();
        $b = BusinessActivity::factory()->for($category, 'category')->create(['sort_order' => 2]);
        $a = BusinessActivity::factory()->for($category, 'category')->create(['sort_order' => 1]);
        $c = BusinessActivity::factory()->for($category, 'category')->create(['sort_order' => 1]);

        $this->assertSame([$a->id, $c->id, $b->id], $category->activities->pluck('id')->all());
        $this->assertTrue($a->category->is($category));
    }

    public function test_published_scope_hides_unpublished_rows(): void
    {
        ServiceCategory::factory()->create(['is_published' => false]);
        ServiceCategory::factory()->create();

        $this->assertSame(1, ServiceCategory::published()->count());
    }

    public function test_image_url_keeps_external_urls_and_resolves_storage_paths(): void
    {
        $external = ServiceCategory::factory()->make(['image_path' => 'https://example.com/a.jpg']);
        $local = ServiceCategory::factory()->make(['image_path' => 'categories/a.jpg']);
        $none = ServiceCategory::factory()->make(['image_path' => null]);

        $this->assertSame('https://example.com/a.jpg', $external->image_url);
        $this->assertStringEndsWith('/storage/categories/a.jpg', $local->image_url);
        $this->assertNull($none->image_url);
    }
}
