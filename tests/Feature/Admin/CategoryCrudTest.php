<?php

namespace Tests\Feature\Admin;

use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->create());
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Kategori Uji',
            'slug' => '',
            'description' => 'Deskripsi uji.',
            'sort_order' => 3,
            'is_published' => 1,
        ], $overrides);
    }

    public function test_guest_cannot_access_category_routes(): void
    {
        auth()->logout();
        $category = ServiceCategory::factory()->create();

        $this->get('/admin/categories')->assertRedirect('/login');
        $this->get('/admin/categories/create')->assertRedirect('/login');
        $this->post('/admin/categories', $this->payload())->assertRedirect('/login');
        $this->get("/admin/categories/{$category->id}/edit")->assertRedirect('/login');
        $this->put("/admin/categories/{$category->id}", $this->payload())->assertRedirect('/login');
        $this->delete("/admin/categories/{$category->id}")->assertRedirect('/login');

        $this->assertSame(1, ServiceCategory::count());
    }

    public function test_index_lists_categories_with_activity_counts_and_empty_state(): void
    {
        $this->get('/admin/categories')->assertOk()->assertSee('Belum ada kategori');

        $category = ServiceCategory::factory()->create(['name' => 'Alfa']);
        BusinessActivity::factory()->count(2)->for($category, 'category')->create();

        $this->get('/admin/categories')
            ->assertOk()
            ->assertSee('Alfa')
            ->assertDontSee('Belum ada kategori');
    }

    public function test_toolbar_puts_the_count_beside_search_and_the_add_button_on_the_right(): void
    {
        ServiceCategory::factory()->create();

        $this->get('/admin/categories')
            ->assertOk()
            ->assertSeeInOrder(['Cari nama atau slug', 'Cari', '1 kategori', '+ Tambah Kategori'], false);
    }

    public function test_index_search_filters_by_name_and_slug(): void
    {
        ServiceCategory::factory()->create(['name' => 'Teknologi', 'slug' => 'teknologi']);
        ServiceCategory::factory()->create(['name' => 'Konstruksi', 'slug' => 'bangunan']);

        $this->get('/admin/categories?q=tekno')->assertSee('Teknologi')->assertDontSee('Konstruksi');
        $this->get('/admin/categories?q=bangunan')->assertSee('Konstruksi')->assertDontSee('Teknologi');
        $this->get('/admin/categories?q=zzz')->assertSee('Tidak ada kategori yang cocok');
    }

    public function test_create_form_renders(): void
    {
        $this->get('/admin/categories/create')->assertOk()->assertSee('Tambah Kategori');
    }

    public function test_store_creates_category_with_generated_slug_and_webp_image(): void
    {
        $this->post('/admin/categories', $this->payload([
            'name' => 'Teknologi & Digital Baru',
            'image' => UploadedFile::fake()->image('cover.jpg', 3000, 1500),
        ]))->assertRedirect('/admin/categories')->assertSessionHas('success');

        $category = ServiceCategory::firstOrFail();

        $this->assertSame('teknologi-digital-baru', $category->slug);
        $this->assertSame(3, $category->sort_order);
        $this->assertTrue($category->is_published);
        $this->assertMatchesRegularExpression('#^categories/[0-9a-f-]{36}\.webp$#', $category->image_path);
        Storage::disk('public')->assertExists($category->image_path);
        $this->assertSame(IMAGETYPE_WEBP, getimagesizefromstring(Storage::disk('public')->get($category->image_path))[2]);
    }

    public function test_store_requires_name_and_keeps_old_input_on_error(): void
    {
        $this->from('/admin/categories/create')
            ->post('/admin/categories', $this->payload(['name' => '', 'description' => 'Teks yang harus tetap ada']))
            ->assertRedirect('/admin/categories/create')
            ->assertSessionHasErrors('name')
            ->assertSessionHasInput('description', 'Teks yang harus tetap ada');

        $this->assertSame(0, ServiceCategory::count());
    }

    public function test_slug_must_be_unique_and_well_formed(): void
    {
        ServiceCategory::factory()->create(['slug' => 'sudah-ada']);

        $this->post('/admin/categories', $this->payload(['slug' => 'sudah-ada']))->assertSessionHasErrors('slug');
        $this->post('/admin/categories', $this->payload(['slug' => 'Tidak Valid!']))->assertSessionHasErrors('slug');
        $this->post('/admin/categories', $this->payload(['slug' => 'dua--tanda']))->assertSessionHasErrors('slug');

        $this->assertSame(1, ServiceCategory::count());
    }

    public function test_invalid_uploads_are_rejected(): void
    {
        $cases = [
            'teks' => UploadedFile::fake()->create('catatan.txt', 10, 'text/plain'),
            'svg' => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            'php-menyamar' => UploadedFile::fake()->createWithContent('shell.jpg', '<?php system($_GET["c"]);'),
            'terlalu-besar' => UploadedFile::fake()->image('besar.jpg', 100, 100)->size(6000),
        ];

        foreach ($cases as $label => $file) {
            $this->post('/admin/categories', $this->payload(['name' => "Uji {$label}", 'image' => $file]))
                ->assertSessionHasErrors('image');
        }

        $this->assertSame(0, ServiceCategory::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_edit_form_renders_with_existing_values(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Nama Lama']);

        $this->get("/admin/categories/{$category->id}/edit")
            ->assertOk()
            ->assertSee('Nama Lama')
            ->assertSee('Mengubah slug');
    }

    public function test_update_changes_fields_and_allows_keeping_own_slug(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Lama', 'slug' => 'lama']);

        $this->put("/admin/categories/{$category->id}", $this->payload(['name' => 'Baru', 'slug' => 'lama', 'is_published' => 0]))
            ->assertRedirect('/admin/categories')
            ->assertSessionHasNoErrors();

        $category->refresh();
        $this->assertSame('Baru', $category->name);
        $this->assertSame('lama', $category->slug);
        $this->assertFalse($category->is_published);
    }

    public function test_update_cannot_take_another_categorys_slug(): void
    {
        ServiceCategory::factory()->create(['slug' => 'milik-lain']);
        $category = ServiceCategory::factory()->create(['slug' => 'milik-saya']);

        $this->put("/admin/categories/{$category->id}", $this->payload(['slug' => 'milik-lain']))
            ->assertSessionHasErrors('slug');

        $this->assertSame('milik-saya', $category->refresh()->slug);
    }

    public function test_replacing_image_deletes_the_old_file(): void
    {
        $this->post('/admin/categories', $this->payload(['image' => UploadedFile::fake()->image('a.jpg')]));
        $category = ServiceCategory::firstOrFail();
        $old = $category->image_path;

        $this->put("/admin/categories/{$category->id}", $this->payload(['slug' => $category->slug, 'image' => UploadedFile::fake()->image('b.png')]))
            ->assertSessionHasNoErrors();

        $category->refresh();
        $this->assertNotSame($old, $category->image_path);
        Storage::disk('public')->assertExists($category->image_path);
        Storage::disk('public')->assertMissing($old);
    }

    public function test_remove_image_clears_path_and_deletes_file(): void
    {
        $this->post('/admin/categories', $this->payload(['image' => UploadedFile::fake()->image('a.jpg')]));
        $category = ServiceCategory::firstOrFail();
        $old = $category->image_path;

        $this->put("/admin/categories/{$category->id}", $this->payload(['slug' => $category->slug, 'remove_image' => 1]))
            ->assertSessionHasNoErrors();

        $this->assertNull($category->refresh()->image_path);
        Storage::disk('public')->assertMissing($old);
    }

    public function test_update_without_new_image_keeps_existing_image(): void
    {
        $this->post('/admin/categories', $this->payload(['image' => UploadedFile::fake()->image('a.jpg')]));
        $category = ServiceCategory::firstOrFail();
        $path = $category->image_path;

        $this->put("/admin/categories/{$category->id}", $this->payload(['slug' => $category->slug, 'name' => 'Ganti Nama']));

        $this->assertSame($path, $category->refresh()->image_path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_replacing_placeholder_url_does_not_try_to_delete_anything(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'ph', 'image_path' => 'https://images.unsplash.com/x.jpg']);

        $this->put("/admin/categories/{$category->id}", $this->payload(['slug' => 'ph', 'image' => UploadedFile::fake()->image('a.jpg')]))
            ->assertSessionHasNoErrors();

        $this->assertStringStartsWith('categories/', $category->refresh()->image_path);
    }

    public function test_category_with_activities_cannot_be_deleted(): void
    {
        $category = ServiceCategory::factory()->create();
        BusinessActivity::factory()->for($category, 'category')->create();

        $this->delete("/admin/categories/{$category->id}")
            ->assertRedirect('/admin/categories')
            ->assertSessionHas('error');

        $this->assertModelExists($category);
        $this->assertSame(1, BusinessActivity::count());
    }

    public function test_empty_category_is_deleted_together_with_its_image_file(): void
    {
        $this->post('/admin/categories', $this->payload(['image' => UploadedFile::fake()->image('a.jpg')]));
        $category = ServiceCategory::firstOrFail();
        $path = $category->image_path;

        $this->delete("/admin/categories/{$category->id}")
            ->assertRedirect('/admin/categories')
            ->assertSessionHas('success');

        $this->assertModelMissing($category);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_new_category_appears_on_public_site_and_hidden_one_does_not(): void
    {
        $this->post('/admin/categories', $this->payload(['name' => 'Tampil Publik', 'is_published' => 1]));
        $this->post('/admin/categories', $this->payload(['name' => 'Sembunyi Publik', 'is_published' => 0]));

        auth()->logout();

        $this->get('/layanan-jasa')->assertSee('Tampil Publik')->assertDontSee('Sembunyi Publik');
        $this->get('/layanan-jasa/tampil-publik')->assertOk();
        $this->get('/layanan-jasa/sembunyi-publik')->assertNotFound();
    }
}
