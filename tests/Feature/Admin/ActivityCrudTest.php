<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityImage;
use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActivityCrudTest extends TestCase
{
    use RefreshDatabase;

    private ServiceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $this->category = ServiceCategory::factory()->create(['name' => 'Kategori Induk', 'slug' => 'induk']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'service_category_id' => $this->category->id,
            'kbli_code' => '',
            'name' => 'Aktivitas Uji',
            'slug' => '',
            'short_description' => 'Singkat.',
            'description' => 'Detail.',
            'sort_order' => 1,
            'is_published' => 1,
        ], $overrides);
    }

    public function test_guest_cannot_access_activity_routes(): void
    {
        auth()->logout();
        $activity = BusinessActivity::factory()->create();

        $this->get('/admin/activities')->assertRedirect('/login');
        $this->get('/admin/activities/create')->assertRedirect('/login');
        $this->post('/admin/activities', $this->payload())->assertRedirect('/login');
        $this->get("/admin/activities/{$activity->id}/edit")->assertRedirect('/login');
        $this->put("/admin/activities/{$activity->id}", $this->payload())->assertRedirect('/login');
        $this->delete("/admin/activities/{$activity->id}")->assertRedirect('/login');
    }

    public function test_index_lists_filters_and_searches(): void
    {
        $other = ServiceCategory::factory()->create(['name' => 'Kategori Lain']);
        BusinessActivity::factory()->for($this->category, 'category')->create(['name' => 'Alfa Satu', 'kbli_code' => '46510']);
        BusinessActivity::factory()->for($other, 'category')->create(['name' => 'Beta Dua']);

        $this->get('/admin/activities')->assertOk()->assertSee('Alfa Satu')->assertSee('Beta Dua');
        $this->get("/admin/activities?category={$this->category->id}")->assertSee('Alfa Satu')->assertDontSee('Beta Dua');
        $this->get('/admin/activities?q=46510')->assertSee('Alfa Satu')->assertDontSee('Beta Dua');
        $this->get('/admin/activities?q=zzz')->assertSee('Tidak ada aktivitas yang cocok');
    }

    public function test_toolbar_puts_the_count_beside_search_and_the_add_button_on_the_right(): void
    {
        BusinessActivity::factory()->for($this->category, 'category')->create();

        $this->get('/admin/activities')
            ->assertOk()
            ->assertSeeInOrder(['Cari nama, slug, atau KBLI', 'Cari', '1 aktivitas', '+ Tambah Aktivitas'], false);
    }

    public function test_empty_state_is_shown_without_activities(): void
    {
        $this->get('/admin/activities')->assertOk()->assertSee('Belum ada aktivitas');
    }

    public function test_create_form_preselects_category_from_query(): void
    {
        $this->get("/admin/activities/create?category={$this->category->id}")
            ->assertOk()
            ->assertSee('Kategori Induk');
    }

    public function test_store_creates_activity_with_generated_slug_and_webp_image(): void
    {
        $this->post('/admin/activities', $this->payload([
            'name' => 'Perdagangan Besar Baru',
            'image' => UploadedFile::fake()->image('foto.png', 2400, 1200),
        ]))->assertRedirect()->assertSessionHas('success');

        $activity = BusinessActivity::firstOrFail();

        $this->assertSame('perdagangan-besar-baru', $activity->slug);
        $this->assertSame($this->category->id, $activity->service_category_id);
        $this->assertNull($activity->kbli_code);
        $this->assertMatchesRegularExpression('#^activities/[0-9a-f-]{36}\.webp$#', $activity->image_path);
        Storage::disk('public')->assertExists($activity->image_path);
    }

    public function test_kbli_code_is_kept_as_string_with_leading_zeros(): void
    {
        $this->post('/admin/activities', $this->payload(['kbli_code' => '01110']))->assertSessionHasNoErrors();

        $this->assertSame('01110', BusinessActivity::firstOrFail()->kbli_code);
    }

    public function test_invalid_kbli_code_is_rejected(): void
    {
        $this->post('/admin/activities', $this->payload(['kbli_code' => 'bukan kode!']))->assertSessionHasErrors('kbli_code');
        $this->post('/admin/activities', $this->payload(['kbli_code' => '12345678901']))->assertSessionHasErrors('kbli_code');

        $this->assertSame(0, BusinessActivity::count());
    }

    public function test_category_is_required_and_must_exist(): void
    {
        $this->post('/admin/activities', $this->payload(['service_category_id' => '']))->assertSessionHasErrors('service_category_id');
        $this->post('/admin/activities', $this->payload(['service_category_id' => 9999]))->assertSessionHasErrors('service_category_id');

        $this->assertSame(0, BusinessActivity::count());
    }

    public function test_validation_error_keeps_old_input(): void
    {
        $this->from('/admin/activities/create')
            ->post('/admin/activities', $this->payload(['name' => '', 'description' => 'Jangan hilang']))
            ->assertRedirect('/admin/activities/create')
            ->assertSessionHasErrors('name')
            ->assertSessionHasInput('description', 'Jangan hilang');
    }

    public function test_slug_is_unique_only_within_the_same_category(): void
    {
        $other = ServiceCategory::factory()->create();
        BusinessActivity::factory()->for($this->category, 'category')->create(['slug' => 'kembar']);

        $this->post('/admin/activities', $this->payload(['slug' => 'kembar']))->assertSessionHasErrors('slug');

        $this->post('/admin/activities', $this->payload(['slug' => 'kembar', 'service_category_id' => $other->id]))
            ->assertSessionHasNoErrors();

        $this->assertSame(2, BusinessActivity::where('slug', 'kembar')->count());
    }

    public function test_slug_format_is_validated(): void
    {
        $this->post('/admin/activities', $this->payload(['slug' => 'Tidak Valid']))->assertSessionHasErrors('slug');
        $this->post('/admin/activities', $this->payload(['slug' => '-awal']))->assertSessionHasErrors('slug');
    }

    public function test_invalid_uploads_are_rejected(): void
    {
        $this->post('/admin/activities', $this->payload(['image' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')]))
            ->assertSessionHasErrors('image');
        $this->post('/admin/activities', $this->payload(['name' => 'B', 'image' => UploadedFile::fake()->createWithContent('b.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>')]))
            ->assertSessionHasErrors('image');

        $this->assertSame(0, BusinessActivity::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_update_keeps_own_slug_can_change_category_and_toggle_status(): void
    {
        $other = ServiceCategory::factory()->create();
        $activity = BusinessActivity::factory()->for($this->category, 'category')->create(['slug' => 'tetap']);

        $this->put("/admin/activities/{$activity->id}", $this->payload([
            'slug' => 'tetap',
            'name' => 'Nama Baru',
            'service_category_id' => $other->id,
            'is_published' => 0,
        ]))->assertSessionHasNoErrors();

        $activity->refresh();
        $this->assertSame('Nama Baru', $activity->name);
        $this->assertSame($other->id, $activity->service_category_id);
        $this->assertFalse($activity->is_published);
    }

    public function test_update_cannot_collide_with_sibling_slug(): void
    {
        BusinessActivity::factory()->for($this->category, 'category')->create(['slug' => 'saudara']);
        $activity = BusinessActivity::factory()->for($this->category, 'category')->create(['slug' => 'saya']);

        $this->put("/admin/activities/{$activity->id}", $this->payload(['slug' => 'saudara']))->assertSessionHasErrors('slug');
    }

    public function test_replace_and_remove_main_image_clean_up_files(): void
    {
        $this->post('/admin/activities', $this->payload(['image' => UploadedFile::fake()->image('a.jpg')]));
        $activity = BusinessActivity::firstOrFail();
        $first = $activity->image_path;

        $this->put("/admin/activities/{$activity->id}", $this->payload(['slug' => $activity->slug, 'image' => UploadedFile::fake()->image('b.jpg')]));
        $activity->refresh();
        $second = $activity->image_path;

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        $this->put("/admin/activities/{$activity->id}", $this->payload(['slug' => $activity->slug, 'remove_image' => 1]));

        $this->assertNull($activity->refresh()->image_path);
        Storage::disk('public')->assertMissing($second);
    }

    public function test_deleting_activity_removes_gallery_rows_and_files(): void
    {
        $this->post('/admin/activities', $this->payload(['image' => UploadedFile::fake()->image('a.jpg')]));
        $activity = BusinessActivity::firstOrFail();

        $galleryPath = 'activities/galeri-satu.webp';
        Storage::disk('public')->put($galleryPath, 'x');
        ActivityImage::factory()->for($activity, 'activity')->create(['image_path' => $galleryPath]);

        $main = $activity->image_path;

        $this->delete("/admin/activities/{$activity->id}")->assertRedirect()->assertSessionHas('success');

        $this->assertModelMissing($activity);
        $this->assertSame(0, ActivityImage::count());
        Storage::disk('public')->assertMissing($main);
        Storage::disk('public')->assertMissing($galleryPath);
    }

    public function test_deleting_activity_keeps_files_still_used_elsewhere(): void
    {
        $shared = 'activities/dipakai-bersama.webp';
        Storage::disk('public')->put($shared, 'x');

        $a = BusinessActivity::factory()->for($this->category, 'category')->create(['image_path' => $shared]);
        BusinessActivity::factory()->for($this->category, 'category')->create(['image_path' => $shared]);

        $this->delete("/admin/activities/{$a->id}");

        Storage::disk('public')->assertExists($shared);
    }

    public function test_deleting_activity_with_placeholder_urls_works(): void
    {
        $activity = BusinessActivity::factory()->for($this->category, 'category')->create(['image_path' => 'https://images.unsplash.com/x.jpg']);
        ActivityImage::factory()->for($activity, 'activity')->create(['image_path' => 'https://images.unsplash.com/y.jpg']);

        $this->delete("/admin/activities/{$activity->id}")->assertSessionHas('success');

        $this->assertModelMissing($activity);
    }

    public function test_new_activity_appears_on_public_site_unless_draft(): void
    {
        $this->post('/admin/activities', $this->payload(['name' => 'Tayang Publik', 'is_published' => 1]));
        $this->post('/admin/activities', $this->payload(['name' => 'Draft Publik', 'is_published' => 0]));

        auth()->logout();

        $this->get('/layanan-jasa/induk')->assertSee('Tayang Publik')->assertDontSee('Draft Publik');
        $this->get('/layanan-jasa/induk/tayang-publik')->assertOk();
        $this->get('/layanan-jasa/induk/draft-publik')->assertNotFound();
    }

    public function test_dashboard_shows_counts(): void
    {
        BusinessActivity::factory()->count(2)->for($this->category, 'category')->create();
        BusinessActivity::factory()->for($this->category, 'category')->create(['is_published' => false]);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('2 terbit, 1 draft')
            ->assertSee('Kelola konten website dengan mudah.')
            ->assertSee(route('admin.categories.create'), false)
            ->assertSee(route('admin.activities.create'), false);
    }
}
