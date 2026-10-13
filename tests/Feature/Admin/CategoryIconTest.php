<?php

namespace Tests\Feature\Admin;

use App\Models\ServiceCategory;
use App\Models\User;
use App\Support\CategoryIcons;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryIconTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function admin(): self
    {
        return $this->actingAs(User::factory()->create());
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Kategori Ikon',
            'slug' => '',
            'sort_order' => 1,
            'is_published' => 1,
        ], $overrides);
    }

    public function test_every_whitelisted_icon_has_a_blade_component_that_renders(): void
    {
        $this->assertGreaterThanOrEqual(20, count(CategoryIcons::keys()));

        foreach (CategoryIcons::keys() as $key) {
            $this->assertTrue(view()->exists("components.icons.{$key}"), "Komponen ikon '{$key}' tidak ditemukan.");

            $html = (string) $this->blade('<x-dynamic-component :component="$component" />', ['component' => 'icons.'.$key]);
            $this->assertStringContainsString('<svg', $html, $key);
            $this->assertStringContainsString('currentColor', $html, $key);
        }
    }

    public function test_new_custom_icons_honor_size_classes_without_default_conflict(): void
    {
        $html = (string) $this->blade('<x-icons.truck class="h-5 w-5" />');

        $this->assertStringContainsString('h-5 w-5', $html);
        $this->assertStringNotContainsString('h-11', $html);
        $this->assertStringContainsString('h-11 w-11', (string) $this->blade('<x-icons.truck />'));
    }

    public function test_seeded_categories_keep_their_original_icons(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $icons = ServiceCategory::ordered()->pluck('icon', 'slug')->all();

        $this->assertSame([
            'technology-digital' => 'computer',
            'home-lifestyle' => 'house',
            'sports-music' => 'sports',
            'industrial-machinery' => 'gearcog',
            'construction-supplies' => 'warehouse',
        ], $icons);
    }

    public function test_category_without_icon_falls_back_by_slug_then_default(): void
    {
        $this->assertSame('computer', ServiceCategory::factory()->make(['slug' => 'technology-digital', 'icon' => null])->resolved_icon);
        $this->assertSame('warehouse', ServiceCategory::factory()->make(['slug' => 'lain-lain', 'icon' => null])->resolved_icon);
        $this->assertSame('truck', ServiceCategory::factory()->make(['slug' => 'lain-lain', 'icon' => 'truck'])->resolved_icon);
        // Nilai di luar whitelist tidak pernah dirender.
        $this->assertSame('warehouse', ServiceCategory::factory()->make(['slug' => 'lain-lain', 'icon' => '../../x'])->resolved_icon);
    }

    public function test_admin_can_choose_a_whitelisted_icon(): void
    {
        $this->admin()->post('/admin/categories', $this->payload(['icon' => 'truck']))->assertSessionHasNoErrors();

        $this->assertSame('truck', ServiceCategory::firstOrFail()->icon);
    }

    public function test_icon_outside_whitelist_is_rejected(): void
    {
        foreach (['../../etc/passwd', 'tidak-ada', '<svg onload=alert(1)>', 'x" onerror="y'] as $bad) {
            $this->admin()->post('/admin/categories', $this->payload(['name' => 'Uji '.md5($bad), 'icon' => $bad]))
                ->assertSessionHasErrors('icon');
        }

        $this->assertSame(0, ServiceCategory::count());
    }

    public function test_create_and_edit_forms_show_every_icon_option(): void
    {
        $category = ServiceCategory::factory()->create(['icon' => 'bolt']);

        $edit = $this->admin()->get("/admin/categories/{$category->id}/edit")->assertOk();
        $this->admin()->get('/admin/categories/create')->assertOk();

        foreach (CategoryIcons::options() as $key => $label) {
            $edit->assertSee('value="'.$key.'"', false)->assertSee($label);
        }
    }

    public function test_admin_can_upload_a_custom_icon_as_small_webp(): void
    {
        $this->admin()->post('/admin/categories', $this->payload([
            'icon' => 'truck',
            'icon_image' => UploadedFile::fake()->image('ikon.png', 1200, 1200),
        ]))->assertSessionHasNoErrors();

        $category = ServiceCategory::firstOrFail();

        $this->assertMatchesRegularExpression('#^category-icons/[0-9a-f-]{36}\.webp$#', $category->icon_image_path);
        $info = getimagesizefromstring(Storage::disk('public')->get($category->icon_image_path));
        $this->assertSame(IMAGETYPE_WEBP, $info[2]);
        $this->assertSame(256, $info[0]);
    }

    public function test_svg_and_non_image_custom_icons_are_rejected(): void
    {
        $svg = UploadedFile::fake()->createWithContent('i.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $txt = UploadedFile::fake()->create('i.txt', 5, 'text/plain');

        $this->admin()->post('/admin/categories', $this->payload(['icon_image' => $svg]))->assertSessionHasErrors('icon_image');
        $this->admin()->post('/admin/categories', $this->payload(['icon_image' => $txt]))->assertSessionHasErrors('icon_image');

        $this->assertSame(0, ServiceCategory::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_failed_icon_upload_does_not_leave_the_main_image_behind(): void
    {
        $this->admin()->post('/admin/categories', $this->payload([
            'image' => UploadedFile::fake()->image('cover.jpg'),
            'icon_image' => UploadedFile::fake()->createWithContent('palsu.png', 'bukan gambar'),
        ]))->assertSessionHasErrors('icon_image');

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_replacing_and_removing_custom_icon_cleans_files(): void
    {
        $this->admin()->post('/admin/categories', $this->payload(['icon_image' => UploadedFile::fake()->image('a.png', 300, 300)]));
        $category = ServiceCategory::firstOrFail();
        $first = $category->icon_image_path;

        $this->admin()->put("/admin/categories/{$category->id}", $this->payload(['slug' => $category->slug, 'icon_image' => UploadedFile::fake()->image('b.png', 300, 300)]))
            ->assertSessionHasNoErrors();
        $category->refresh();
        $second = $category->icon_image_path;

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        $this->admin()->put("/admin/categories/{$category->id}", $this->payload(['slug' => $category->slug, 'remove_icon_image' => 1]));

        $this->assertNull($category->refresh()->icon_image_path);
        Storage::disk('public')->assertMissing($second);
    }

    public function test_deleting_category_removes_its_icon_file(): void
    {
        $this->admin()->post('/admin/categories', $this->payload(['icon_image' => UploadedFile::fake()->image('a.png', 300, 300)]));
        $category = ServiceCategory::firstOrFail();
        $path = $category->icon_image_path;

        $this->admin()->delete("/admin/categories/{$category->id}");

        Storage::disk('public')->assertMissing($path);
    }

    public function test_home_card_uses_chosen_whitelist_icon_and_custom_image_when_present(): void
    {
        ServiceCategory::factory()->create(['name' => 'Kartu Truk', 'slug' => 'kartu-truk', 'icon' => 'truck', 'sort_order' => 1]);
        ServiceCategory::factory()->create([
            'name' => 'Kartu Kustom', 'slug' => 'kartu-kustom', 'icon' => 'truck',
            'icon_image_path' => 'category-icons/kustom.webp', 'sort_order' => 2,
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        // Truk: markup SVG ikon truck (dua lingkaran roda) muncul untuk kartu pertama saja.
        $this->assertSame(1, substr_count($html, 'cx="6.5" cy="17.5"'));
        // Kartu kedua memakai gambar kustom, bukan SVG.
        $this->assertStringContainsString('/storage/category-icons/kustom.webp', $html);
    }
}
