<?php

namespace Tests\Feature\Catalog;

use App\Models\ActivityImage;
use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_home_service_cards_have_a_fixed_size_regardless_of_description_length(): void
    {
        ServiceCategory::factory()->create(['name' => 'Pendek', 'slug' => 'pendek', 'description' => 'Singkat.', 'sort_order' => 1]);
        ServiceCategory::factory()->create([
            'name' => 'Judul Kategori Yang Sangat Panjang Sekali Sampai Melewati Dua Baris Penuh Di Kartu',
            'slug' => 'panjang',
            'description' => str_repeat('Deskripsi sangat panjang. ', 30),
            'sort_order' => 2,
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        // Dua kartu dengan isi berbeda memakai tinggi tetap yang sama.
        $this->assertSame(2, substr_count($html, 'flex h-[320px] flex-col'));
        $this->assertStringNotContainsString('min-h-[280px]', $html);
        // Judul dan deskripsi dipotong agar tidak mengubah ukuran kartu.
        $this->assertSame(2, substr_count($html, 'line-clamp-2'));
        $this->assertSame(2, substr_count($html, 'line-clamp-4'));
    }

    public function test_home_service_cards_have_all_five_seeded_categories(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('/layanan-jasa/construction-supplies#construction-supplies', false)
            ->assertSee('/layanan-jasa/technology-digital#technology-digital', false);
    }

    private function makeCategories(int $count, array $overrides = []): void
    {
        foreach (range(1, $count) as $i) {
            ServiceCategory::factory()->create(array_merge([
                'name' => sprintf('Kategori Nomor %02d', $i),
                'slug' => sprintf('kategori-%02d', $i),
                'sort_order' => $i,
            ], $overrides));
        }
    }

    public function test_home_service_cards_stay_a_static_grid_with_six_or_fewer_categories(): void
    {
        $this->makeCategories(6);

        $this->get('/')
            ->assertOk()
            ->assertSee('Kategori Nomor 06')
            ->assertDontSee('serviceCarousel(', false)
            ->assertDontSee('aria-roledescription="carousel"', false)
            ->assertSee('class="mt-16 grid grid-cols-2 gap-2 sm:gap-3 lg:grid-cols-3"', false);
    }

    public function test_home_service_cards_become_an_auto_sliding_carousel_above_six_categories(): void
    {
        $this->makeCategories(7);

        $this->get('/')
            ->assertOk()
            ->assertSee('x-data="serviceCarousel(2)"', false)
            ->assertSee('aria-roledescription="carousel"', false)
            // semua kategori tetap ada, dibagi dua halaman (6 + 1)
            ->assertSeeInOrder(['Kategori Nomor 01', 'Kategori Nomor 06', 'Kategori Nomor 07'])
            ->assertSee('aria-label="1 dari 2"', false)
            ->assertSee('aria-label="2 dari 2"', false)
            // nomor kartu berlanjut di halaman kedua
            ->assertSee('07')
            // kontrol: titik halaman dan tombol sebelumnya/berikutnya
            ->assertSee('aria-label="Halaman 1"', false)
            ->assertSee('aria-label="Halaman 2"', false)
            ->assertSee('aria-label="Sebelumnya"', false)
            ->assertSee('aria-label="Berikutnya"', false);
    }

    public function test_carousel_arrows_flank_the_cards_without_borders_and_page_info_is_centered(): void
    {
        $this->makeCategories(7);

        $html = $this->get('/')->assertOk()->getContent();

        // Panah samping (layar lebar): absolute di kiri/kanan kartu, tanpa kelas border.
        $this->assertSame(1, substr_count($html, '-left-12 top-1/2'));
        $this->assertSame(1, substr_count($html, '-right-12 top-1/2'));
        // Panah cadangan (layar sempit) mengapit informasi halaman yang rata tengah.
        $this->assertSame(2, substr_count($html, 'min-[1366px]:hidden'));
        $this->assertStringContainsString('mt-6 flex items-center justify-center gap-3', $html);
        // Tombol panah tidak punya border.
        preg_match_all('#<button[^>]*aria-label="(?:Sebelumnya|Berikutnya)"[^>]*class="([^"]*)"#', $html, $m);
        $this->assertCount(4, $m[1]);
        foreach ($m[1] as $class) {
            $this->assertStringNotContainsString('border', $class);
        }
    }

    private function desktopDropdown(string $html): string
    {
        $start = strpos($html, 'id="desktop-services"');
        $this->assertNotFalse($start, 'Dropdown desktop tidak ditemukan.');

        return substr($html, $start, strpos($html, 'href="'.route('contact').'"', $start) - $start);
    }

    public function test_mobile_menu_scrolls_inside_itself_so_long_category_lists_stay_reachable(): void
    {
        $this->makeCategories(14);

        $html = $this->get('/layanan-jasa')->assertOk()->getContent();

        preg_match('#id="mobile-navigation"(.*?)class="([^"]*)"#s', $html, $m);

        $this->assertNotEmpty($m, 'Panel menu mobile tidak ditemukan.');
        // Tinggi dibatasi sebatas layar dikurangi baris header (h-20 = 5rem) dan bisa digulir.
        $this->assertStringContainsString('max-h-[calc(100dvh-5rem)]', $m[2]);
        $this->assertStringContainsString('overflow-y-auto', $m[2]);
        $this->assertStringContainsString('overscroll-contain', $m[2]);
        // Semua kategori tetap tersedia di menu mobile.
        $this->assertStringContainsString('Kategori Nomor 14', $html);
    }

    public function test_navbar_dropdown_is_unchanged_for_six_or_fewer_categories(): void
    {
        $this->makeCategories(6);

        $dropdown = $this->desktopDropdown($this->get('/tentang-kami')->assertOk()->getContent());

        $this->assertStringContainsString('class="absolute right-0 top-full z-50 mt-4 w-72 border border-slate-200 bg-white shadow-lg"', $dropdown);
        $this->assertStringContainsString('Semua Layanan', $dropdown);
        $this->assertStringContainsString('Kategori Nomor 06', $dropdown);
        $this->assertStringNotContainsString('columns-2', $dropdown);
        $this->assertStringNotContainsString('break-inside-avoid', $dropdown);
        $this->assertStringNotContainsString('Lihat semua', $dropdown);
    }

    public function test_navbar_dropdown_becomes_two_plain_columns_above_six_categories(): void
    {
        $this->makeCategories(9);

        $dropdown = $this->desktopDropdown($this->get('/tentang-kami')->assertOk()->getContent());

        $this->assertStringContainsString('w-[34rem] max-w-[calc(100vw-2rem)]', $dropdown);
        $this->assertStringNotContainsString('w-72', $dropdown);
        $this->assertStringContainsString('columns-2 gap-x-2', $dropdown);
        $this->assertStringContainsString('[column-rule:1px_solid_rgb(226_232_240)]', $dropdown);
        $this->assertSame(9, substr_count($dropdown, 'break-inside-avoid'));
        // Tanpa penomoran: hanya nama kategori.
        $this->assertStringContainsString('Kategori Nomor 09', $dropdown);
        $this->assertDoesNotMatchRegularExpression('#font-bold[^>]*>\s*0[1-9]\s*</span>#', $dropdown);
        $this->assertStringNotContainsString('text-[11px]', $dropdown);
        // Tautan "Semua Layanan" tetap ada di atas, dan tidak ada tautan "Lihat semua" selama <= 12.
        $this->assertStringContainsString('Semua Layanan', $dropdown);
        $this->assertStringNotContainsString('Lihat semua', $dropdown);
    }

    public function test_navbar_dropdown_caps_at_twelve_with_a_see_all_link(): void
    {
        $this->makeCategories(15);

        $dropdown = $this->desktopDropdown($this->get('/tentang-kami')->assertOk()->getContent());

        $this->assertSame(12, substr_count($dropdown, 'break-inside-avoid'));
        $this->assertStringContainsString('Kategori Nomor 12', $dropdown);
        $this->assertStringNotContainsString('Kategori Nomor 13', $dropdown);
        $this->assertStringContainsString('Lihat semua 15 bidang usaha', $dropdown);
    }

    public function test_navbar_dropdown_marks_the_active_category_in_the_two_column_layout(): void
    {
        $this->makeCategories(8);

        $dropdown = $this->desktopDropdown($this->get('/layanan-jasa/kategori-03')->assertOk()->getContent());

        $this->assertSame(1, substr_count($dropdown, 'aria-current="page"'));
        // Penanda aktif berupa titik merah di sisi kanan (tanpa nomor).
        $this->assertSame(1, substr_count($dropdown, 'h-1.5 w-1.5 shrink-0 rounded-full bg-primary'));
        $this->assertMatchesRegularExpression('#href="[^"]*/layanan-jasa/kategori-03"[^>]*aria-current="page"#', $dropdown);
    }

    public function test_footer_keeps_a_single_column_for_six_or_fewer_categories(): void
    {
        $this->makeCategories(6);

        $this->get('/tentang-kami')
            ->assertOk()
            ->assertSee('class="lg:col-span-5"', false)
            ->assertSee('class="lg:col-span-4"', false)
            ->assertSee('class="mt-6 space-y-4"', false)
            ->assertSee('Kategori Nomor 06')
            ->assertDontSee('bidang usaha</', false)
            // tampilan satu kolom tetap polos: tanpa nomor dan tanpa kolom CSS
            ->assertDontSee('break-inside-avoid', false)
            ->assertDontSee('sm:columns-2', false);
    }

    public function test_footer_uses_two_columns_and_a_wider_block_for_seven_to_twelve_categories(): void
    {
        $this->makeCategories(12);

        $html = $this->get('/tentang-kami')->assertOk()->assertSee('Kategori Nomor 12')->getContent();

        // Dua kolom dengan urutan baca turun ke bawah, bernomor, dan garis pemisah halus.
        $this->assertStringContainsString('columns-1 gap-x-10 sm:columns-2', $html);
        $this->assertStringContainsString('[column-rule:1px_solid_rgb(255_255_255/0.08)]', $html);
        $this->assertSame(12, substr_count($html, 'break-inside-avoid pb-3.5'));
        $this->assertMatchesRegularExpression('#group-hover:text-primary"[^>]*>\s*12\s*</span>\s*<span>Kategori Nomor 12</span>#', $html);
        $this->assertStringContainsString('lg:col-span-5', $html);
        $this->assertStringNotContainsString('class="mt-6 space-y-4"><li>', $html);
        $this->assertStringNotContainsString('Lihat semua 12 bidang usaha', $html);
    }

    public function test_footer_caps_the_list_at_twelve_and_links_to_all_services(): void
    {
        $this->makeCategories(15);

        $this->get('/tentang-kami')
            ->assertOk()
            ->assertSee('Kategori Nomor 12')
            ->assertSee('Lihat semua 15 bidang usaha')
            ->assertSee('border border-white/20', false)
            ->assertSee('href="'.route('services').'"', false);

        // Daftar lengkap tetap tersedia di halaman Layanan & Jasa.
        $this->get('/layanan-jasa')->assertSee('Kategori Nomor 15');
    }

    public function test_carousel_page_count_grows_with_categories(): void
    {
        $this->makeCategories(13);

        $this->get('/')
            ->assertOk()
            ->assertSee('x-data="serviceCarousel(3)"', false)
            ->assertSee('aria-label="3 dari 3"', false)
            ->assertSee('Kategori Nomor 13');
    }

    public function test_carousel_only_counts_published_categories(): void
    {
        $this->makeCategories(6);
        ServiceCategory::factory()->create(['name' => 'Kategori Draft Ketujuh', 'slug' => 'draft-ketujuh', 'sort_order' => 7, 'is_published' => false]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('serviceCarousel(', false)
            ->assertDontSee('Kategori Draft Ketujuh');
    }

    public function test_carousel_slides_keep_valid_links_and_inactive_slides_are_inert_by_default(): void
    {
        $this->makeCategories(8);

        $this->get('/')
            ->assertOk()
            ->assertSee('/layanan-jasa/kategori-08#kategori-08', false)
            ->assertSee('x-bind:inert="current !== 1"', false);
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

    public function test_public_lightbox_listens_for_the_event_with_optional_image_list(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'kat']);
        $activity = BusinessActivity::factory()->for($category, 'category')->create(['slug' => 'akt']);
        ActivityImage::factory()->for($activity, 'activity')->create(['image_path' => 'https://example.com/satu.jpg']);

        $this->get('/layanan-jasa/kat/akt')
            ->assertOk()
            ->assertSee('openPreview($event.detail.index, $event.detail.images)', false)
            ->assertSee("\$dispatch('documentation-open', { index: 0 })", false);
    }

    public function test_pages_do_not_run_queries_per_activity(): void
    {
        $category = ServiceCategory::factory()->create(['slug' => 'kat']);
        BusinessActivity::factory()->count(8)->for($category, 'category')->create();

        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });

        $this->get('/layanan-jasa/kat')->assertOk();

        $this->assertLessThanOrEqual(6, $queries);
    }
}
