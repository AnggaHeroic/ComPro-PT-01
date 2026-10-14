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
            ->assertSeeInOrder(['Cari nama atau alamat URL', 'Cari', '1 kategori', '+ Tambah Kategori'], false);
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

    public function test_slug_is_generated_from_the_name_and_made_unique(): void
    {
        $this->post('/admin/categories', $this->payload(['name' => 'Kategori Uji']));
        $this->post('/admin/categories', $this->payload(['name' => 'Kategori Uji']));
        $this->post('/admin/categories', $this->payload(['name' => 'Kategori   UJI!']));

        $this->assertSame(
            ['kategori-uji', 'kategori-uji-2', 'kategori-uji-3'],
            ServiceCategory::orderBy('id')->pluck('slug')->all(),
        );
    }

    public function test_url_can_be_set_manually_and_is_normalized_to_lowercase(): void
    {
        $this->post('/admin/categories', $this->payload(['name' => 'Nama Asli', 'slug' => 'Alamat-Saya']))
            ->assertSessionHasNoErrors();

        $this->assertSame('alamat-saya', ServiceCategory::firstOrFail()->slug);
    }

    public function test_url_must_be_unique_and_well_formed_with_a_friendly_label(): void
    {
        ServiceCategory::factory()->create(['slug' => 'sudah-ada']);

        $dup = $this->post('/admin/categories', $this->payload(['slug' => 'sudah-ada']));
        $dup->assertSessionHasErrors('slug');
        $this->assertSame('Alamat URL sudah dipakai.', session('errors')->first('slug'));

        $this->post('/admin/categories', $this->payload(['slug' => 'Tidak Valid!']))->assertSessionHasErrors('slug');
        $this->post('/admin/categories', $this->payload(['slug' => 'dua--tanda']))->assertSessionHasErrors('slug');
        $this->post('/admin/categories', $this->payload(['slug' => '-awal']))->assertSessionHasErrors('slug');

        $this->assertSame(1, ServiceCategory::count());
    }

    public function test_a_blank_url_is_generated_from_the_name_when_published(): void
    {
        $this->post('/admin/categories', $this->payload(['name' => 'Otomatis Dari Nama', 'slug' => '', 'status' => 'published']));

        $this->assertSame('otomatis-dari-nama', ServiceCategory::firstOrFail()->slug);
    }

    public function test_a_draft_can_still_be_given_a_manual_url(): void
    {
        $this->post('/admin/categories', $this->payload(['name' => 'Draft Beralamat', 'slug' => 'alamat-draft', 'status' => 'draft']));

        $category = ServiceCategory::firstOrFail();
        $this->assertFalse($category->is_published);
        $this->assertSame('alamat-draft', $category->slug);
    }

    public function test_draft_category_gets_no_slug_until_it_is_published(): void
    {
        $this->post('/admin/categories', $this->payload(['name' => 'Masih Draft', 'status' => 'draft']))
            ->assertSessionHasNoErrors();

        $category = ServiceCategory::firstOrFail();
        $this->assertFalse($category->is_published);
        $this->assertNull($category->slug);

        $this->put("/admin/categories/{$category->id}", $this->payload(['name' => 'Masih Draft', 'status' => 'published']));
        $this->assertSame('masih-draft', $category->refresh()->slug);
        $this->assertTrue($category->is_published);
    }

    public function test_url_stays_the_same_when_renamed_or_set_back_to_draft_via_the_form(): void
    {
        $this->post('/admin/categories', $this->payload(['name' => 'Nama Awal', 'status' => 'published']));
        $category = ServiceCategory::firstOrFail();

        // Form edit selalu mengirim alamat URL yang sudah terisi.
        $this->put("/admin/categories/{$category->id}", $this->payload(['name' => 'Nama Baru Sekali', 'slug' => $category->slug, 'status' => 'published']));
        $this->assertSame('nama-awal', $category->refresh()->slug);

        $this->put("/admin/categories/{$category->id}", $this->payload(['name' => 'Nama Baru Sekali', 'slug' => $category->slug, 'status' => 'draft']));
        $category->refresh();
        $this->assertFalse($category->is_published);
        $this->assertSame('nama-awal', $category->slug);

        $this->put("/admin/categories/{$category->id}", $this->payload(['name' => 'Nama Baru Sekali', 'slug' => $category->slug, 'status' => 'published']));
        $this->assertSame('nama-awal', $category->refresh()->slug);
    }

    public function test_clearing_the_url_regenerates_it_from_the_name_only_when_published(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Ganti Alamat', 'slug' => 'alamat-lama']);

        $this->put("/admin/categories/{$category->id}", $this->payload(['name' => 'Ganti Alamat', 'slug' => '', 'status' => 'published']));
        $this->assertSame('ganti-alamat', $category->refresh()->slug);

        $this->put("/admin/categories/{$category->id}", $this->payload(['name' => 'Ganti Alamat', 'slug' => '', 'status' => 'draft']));
        $this->assertNull($category->refresh()->slug);
    }

    public function test_changing_the_url_changes_the_public_address(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Ubah Alamat', 'slug' => 'alamat-lama']);

        $this->put("/admin/categories/{$category->id}", $this->payload(['name' => 'Ubah Alamat', 'slug' => 'alamat-baru']));

        auth()->logout();
        $this->get('/layanan-jasa/alamat-baru')->assertOk();
        $this->get('/layanan-jasa/alamat-lama')->assertNotFound();
    }

    public function test_status_must_be_published_or_draft(): void
    {
        $this->post('/admin/categories', $this->payload(['status' => 'rahasia']))->assertSessionHasErrors('status');

        $this->assertSame(0, ServiceCategory::count());
    }

    public function test_form_offers_a_status_choice_and_a_url_field_without_the_word_slug(): void
    {
        $this->get('/admin/categories/create')
            ->assertOk()
            ->assertSee('name="status" value="published"', false)
            ->assertSee('name="status" value="draft"', false)
            ->assertSee('Terbit')
            ->assertSee('Draft')
            ->assertSee('Alamat URL')
            ->assertSee('name="slug"', false)
            ->assertDontSeeText('slug')
            ->assertDontSeeText('Slug');
    }

    public function test_status_options_use_green_for_published_and_yellow_for_draft(): void
    {
        $html = $this->get('/admin/categories/create')->assertOk()->getContent();

        $this->assertStringContainsString('hover:border-green-500', $html);
        $this->assertStringContainsString('peer-checked:border-green-600', $html);
        $this->assertStringContainsString('hover:border-yellow-500', $html);
        $this->assertStringContainsString('peer-checked:border-yellow-500', $html);
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
        $category = ServiceCategory::factory()->create(['name' => 'Nama Lama', 'slug' => 'alamat-saya']);

        $this->get("/admin/categories/{$category->id}/edit")
            ->assertOk()
            ->assertSee('Nama Lama')
            ->assertSee('value="alamat-saya"', false)
            ->assertSee('Mengubah alamat ini membuat tautan lama tidak berfungsi');
    }

    public function test_update_changes_fields_and_allows_keeping_own_url(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Lama', 'slug' => 'lama']);

        $this->put("/admin/categories/{$category->id}", $this->payload(['name' => 'Baru', 'slug' => 'lama', 'is_published' => 0]))
            ->assertRedirect('/admin/categories?status=draft')
            ->assertSessionHasNoErrors();

        $category->refresh();
        $this->assertSame('Baru', $category->name);
        $this->assertSame('lama', $category->slug);
        $this->assertFalse($category->is_published);
    }

    public function test_update_cannot_take_another_categorys_url(): void
    {
        ServiceCategory::factory()->create(['slug' => 'milik-lain']);
        $category = ServiceCategory::factory()->create(['slug' => 'milik-saya']);

        $this->put("/admin/categories/{$category->id}", $this->payload(['slug' => 'milik-lain']))
            ->assertSessionHasErrors('slug');

        $this->assertSame('milik-saya', $category->refresh()->slug);
    }

    public function test_saving_as_draft_redirects_to_the_draft_list_and_shows_the_item(): void
    {
        $this->post('/admin/categories', $this->payload(['name' => 'Baru Tapi Draft', 'status' => 'draft']))
            ->assertRedirect('/admin/categories?status=draft')
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'sebagai draft') && str_contains($message, 'Belum tampil di website'));

        $this->get('/admin/categories?status=draft')->assertOk()->assertSee('Baru Tapi Draft');
    }

    public function test_publishing_redirects_to_the_normal_list(): void
    {
        $this->post('/admin/categories', $this->payload(['name' => 'Langsung Terbit', 'status' => 'published']))
            ->assertRedirect('/admin/categories');
    }

    public function test_status_tabs_filter_the_list_and_show_counts(): void
    {
        ServiceCategory::factory()->count(2)->create(['is_published' => true]);
        ServiceCategory::factory()->create(['name' => 'Kategori Konsep', 'slug' => null, 'is_published' => false]);

        $all = $this->get('/admin/categories')->assertOk();
        $all->assertSee('Semua')->assertSee('Terbit')->assertSee('Draft')->assertSee('Kategori Konsep');

        $this->get('/admin/categories?status=draft')->assertOk()->assertSee('Kategori Konsep')->assertSee('1 kategori');
        $published = $this->get('/admin/categories?status=published')->assertOk()->assertDontSee('Kategori Konsep');
        $published->assertSee('2 kategori');

        // nilai tak dikenal dianggap "semua"
        $this->get('/admin/categories?status=apa-saja')->assertOk()->assertSee('Kategori Konsep');
    }

    public function test_tab_counts_are_not_affected_by_the_search_and_the_filter_is_kept_when_searching(): void
    {
        ServiceCategory::factory()->create(['name' => 'Alfa Draft', 'slug' => null, 'is_published' => false]);
        ServiceCategory::factory()->create(['name' => 'Beta Draft', 'slug' => null, 'is_published' => false]);

        $html = $this->get('/admin/categories?status=draft&q=Alfa')->assertOk()->assertSee('Alfa Draft')->assertDontSee('Beta Draft')->getContent();

        $this->assertStringContainsString('<input type="hidden" name="status" value="draft">', $html);
        $this->assertStringContainsString('>2</span>', $html);
    }

    public function test_empty_draft_filter_shows_a_clear_message(): void
    {
        ServiceCategory::factory()->create(['is_published' => true]);

        $this->get('/admin/categories?status=draft')->assertOk()->assertSee('Tidak ada kategori draft.');
    }

    public function test_status_badges_use_green_for_published_and_yellow_for_draft(): void
    {
        ServiceCategory::factory()->create(['name' => 'Aktif', 'is_published' => true]);
        ServiceCategory::factory()->create(['name' => 'Konsep', 'slug' => null, 'is_published' => false]);

        $html = $this->get('/admin/categories')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#bg-green-100[^"]*text-green-800">Terbit</span>#', $html);
        $this->assertMatchesRegularExpression('#bg-yellow-100[^"]*text-yellow-800">Draft</span>#', $html);
    }

    public function test_sidebar_shows_the_number_of_drafts(): void
    {
        ServiceCategory::factory()->count(2)->create(['slug' => null, 'is_published' => false]);

        $this->get('/admin')->assertOk()->assertSee('title="2 draft"', false);
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
