<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityImage;
use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\ImageUploader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    private BusinessActivity $activity;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $category = ServiceCategory::factory()->create(['name' => 'Kategori Galeri', 'slug' => 'kat-galeri']);
        $this->activity = BusinessActivity::factory()->for($category, 'category')->create([
            'name' => 'Aktivitas Galeri',
            'slug' => 'akt-galeri',
        ]);
    }

    private function url(string $suffix = ''): string
    {
        return "/admin/gallery/{$this->activity->id}{$suffix}";
    }

    private function photo(string $name = 'foto.jpg', int $w = 800, int $h = 600): UploadedFile
    {
        return UploadedFile::fake()->image($name, $w, $h);
    }

    private function stored(string $name): string
    {
        $path = 'activities/gallery/'.$name.'.webp';
        Storage::disk('public')->put($path, 'x');

        return $path;
    }

    public function test_guest_cannot_access_gallery_routes(): void
    {
        auth()->logout();
        $image = ActivityImage::factory()->for($this->activity, 'activity')->create();

        $this->get('/admin/gallery')->assertRedirect('/login');
        $this->get($this->url())->assertRedirect('/login');
        $this->post($this->url('/images'), ['images' => [$this->photo()]])->assertRedirect('/login');
        $this->patch($this->url("/images/{$image->id}"), [])->assertRedirect('/login');
        $this->delete($this->url("/images/{$image->id}"))->assertRedirect('/login');
        $this->put($this->url('/order'), ['order' => [$image->id]])->assertRedirect('/login');

        $this->assertSame(1, ActivityImage::count());
    }

    public function test_index_lists_activities_with_photo_counts_and_filters(): void
    {
        ActivityImage::factory()->count(3)->for($this->activity, 'activity')->create();
        $other = BusinessActivity::factory()->create(['name' => 'Aktivitas Lain']);

        $this->get('/admin/gallery')
            ->assertOk()
            ->assertSee('Aktivitas Galeri')
            ->assertSee('Kosong')
            ->assertSee('Aktivitas Lain')
            ->assertSee('Kosong');

        $this->get("/admin/gallery?category={$other->service_category_id}")
            ->assertSee('Aktivitas Lain')
            ->assertDontSee('Aktivitas Galeri');

        $this->get('/admin/gallery?q=zzz')->assertSee('Tidak ada aktivitas yang cocok');
    }

    public function test_show_renders_empty_state(): void
    {
        $this->get($this->url())
            ->assertOk()
            ->assertSee('Galeri masih kosong')
            ->assertSee('Tambah foto');
    }

    public function test_show_renders_existing_photos_with_captions(): void
    {
        ActivityImage::factory()->for($this->activity, 'activity')->create(['caption' => 'Gudang utama', 'sort_order' => 1]);

        $this->get($this->url())
            ->assertOk()
            ->assertDontSee('Galeri masih kosong')
            ->assertSee('Gudang utama')
            ->assertSee('Simpan urutan');
    }

    public function test_upload_several_photos_converts_to_webp_and_appends_in_order(): void
    {
        $this->post($this->url('/images'), [
            'images' => [$this->photo('a.jpg', 3000, 1500), $this->photo('b.png'), $this->photo('c.jpg')],
        ])->assertRedirect($this->url())->assertSessionHas('success');

        $images = $this->activity->images()->get();

        $this->assertCount(3, $images);
        $this->assertSame([1, 2, 3], $images->pluck('sort_order')->all());

        foreach ($images as $image) {
            $this->assertMatchesRegularExpression('#^activities/gallery/[0-9a-f-]{36}\.webp$#', $image->image_path);
            Storage::disk('public')->assertExists($image->image_path);
            $this->assertSame(IMAGETYPE_WEBP, getimagesizefromstring(Storage::disk('public')->get($image->image_path))[2]);
        }

        $this->assertSame(2000, getimagesizefromstring(Storage::disk('public')->get($images[0]->image_path))[0]);
    }

    public function test_new_uploads_are_appended_after_existing_photos(): void
    {
        ActivityImage::factory()->for($this->activity, 'activity')->create(['sort_order' => 7]);

        $this->post($this->url('/images'), ['images' => [$this->photo()]]);

        $this->assertSame([7, 8], $this->activity->images()->pluck('sort_order')->all());
    }

    public function test_invalid_files_are_rejected_and_nothing_is_stored(): void
    {
        $bad = [
            UploadedFile::fake()->create('catatan.txt', 5, 'text/plain'),
            UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>1</script></svg>'),
            UploadedFile::fake()->createWithContent('shell.jpg', '<?php system($_GET["c"]);'),
            UploadedFile::fake()->image('besar.jpg', 100, 100)->size(6000),
        ];

        foreach ($bad as $file) {
            $this->post($this->url('/images'), ['images' => [$file]])->assertSessionHasErrors('images.0');
        }

        $this->assertSame(0, ActivityImage::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_a_single_bad_file_rejects_the_whole_batch(): void
    {
        $this->post($this->url('/images'), [
            'images' => [$this->photo('ok.jpg'), UploadedFile::fake()->create('rusak.txt', 5, 'text/plain')],
        ])->assertSessionHasErrors('images.1');

        $this->assertSame(0, ActivityImage::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_processing_failure_in_the_middle_of_a_batch_cleans_up_files_already_stored(): void
    {
        $fake = new class extends ImageUploader
        {
            public int $calls = 0;

            /** @var list<string> */
            public array $released = [];

            public function store(UploadedFile $file, string $directory, ?int $maxWidth = null): string
            {
                if (++$this->calls === 2) {
                    throw new InvalidArgumentException('gagal diproses');
                }

                $path = 'activities/gallery/pertama.webp';
                Storage::disk('public')->put($path, 'x');

                return $path;
            }

            public function release(?string $path): bool
            {
                $this->released[] = $path;
                Storage::disk('public')->delete($path);

                return true;
            }
        };
        app()->instance(ImageUploader::class, $fake);

        $this->post($this->url('/images'), ['images' => [$this->photo('a.jpg'), $this->photo('b.jpg')]])
            ->assertSessionHasErrors('images');

        $this->assertSame(2, $fake->calls);
        $this->assertSame(['activities/gallery/pertama.webp'], $fake->released);
        $this->assertSame(0, ActivityImage::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_upload_requires_at_least_one_file_and_limits_batch_size(): void
    {
        $this->post($this->url('/images'), [])->assertSessionHasErrors('images');

        $tooMany = array_map(fn ($i) => $this->photo("f{$i}.jpg", 50, 50), range(1, 11));
        $this->post($this->url('/images'), ['images' => $tooMany])->assertSessionHasErrors('images');

        $this->assertSame(0, ActivityImage::count());
    }

    public function test_total_gallery_size_is_capped(): void
    {
        config(['images.gallery_max_per_activity' => 3]);
        ActivityImage::factory()->count(2)->for($this->activity, 'activity')->create();

        $this->post($this->url('/images'), ['images' => [$this->photo('a.jpg'), $this->photo('b.jpg')]])
            ->assertSessionHasErrors('images');

        $this->assertSame(2, ActivityImage::count());

        $this->post($this->url('/images'), ['images' => [$this->photo('c.jpg')]])->assertSessionHasNoErrors();
        $this->assertSame(3, ActivityImage::count());
    }

    public function test_caption_can_be_updated_and_cleared(): void
    {
        $image = ActivityImage::factory()->for($this->activity, 'activity')->create(['caption' => null]);

        $this->patch($this->url("/images/{$image->id}"), ['caption' => 'Keterangan baru'])
            ->assertRedirect($this->url())
            ->assertSessionHasNoErrors();
        $this->assertSame('Keterangan baru', $image->refresh()->caption);

        $this->patch($this->url("/images/{$image->id}"), ['caption' => ''])->assertSessionHasNoErrors();
        $this->assertNull($image->refresh()->caption);
    }

    public function test_caption_is_validated_and_error_is_scoped_to_that_photo(): void
    {
        $image = ActivityImage::factory()->for($this->activity, 'activity')->create();

        $this->from($this->url())
            ->patch($this->url("/images/{$image->id}"), ['caption' => str_repeat('a', 300)])
            ->assertRedirect($this->url())
            ->assertSessionHasErrorsIn('image'.$image->id, 'caption');
    }

    public function test_caption_is_escaped_on_output(): void
    {
        ActivityImage::factory()->for($this->activity, 'activity')->create(['caption' => '<script>alert(1)</script>']);

        $this->get($this->url())->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_replacing_a_photo_swaps_the_file_and_keeps_its_position(): void
    {
        $old = $this->stored('lama');
        $image = ActivityImage::factory()->for($this->activity, 'activity')->create(['image_path' => $old, 'sort_order' => 4]);

        $this->patch($this->url("/images/{$image->id}"), ['caption' => 'Baru', 'image' => $this->photo('baru.png')])
            ->assertSessionHasNoErrors();

        $image->refresh();
        $this->assertNotSame($old, $image->image_path);
        $this->assertSame(4, $image->sort_order);
        Storage::disk('public')->assertExists($image->image_path);
        Storage::disk('public')->assertMissing($old);
    }

    public function test_replacing_a_placeholder_url_does_not_delete_anything_external(): void
    {
        $image = ActivityImage::factory()->for($this->activity, 'activity')->create(['image_path' => 'https://images.unsplash.com/x.jpg']);

        $this->patch($this->url("/images/{$image->id}"), ['image' => $this->photo()])->assertSessionHasNoErrors();

        $this->assertStringStartsWith('activities/gallery/', $image->refresh()->image_path);
    }

    public function test_invalid_replacement_keeps_the_original_file(): void
    {
        $old = $this->stored('asli');
        $image = ActivityImage::factory()->for($this->activity, 'activity')->create(['image_path' => $old]);

        $this->from($this->url())
            ->patch($this->url("/images/{$image->id}"), ['image' => UploadedFile::fake()->create('x.txt', 5, 'text/plain')])
            ->assertSessionHasErrorsIn('image'.$image->id, 'image');

        $this->assertSame($old, $image->refresh()->image_path);
        Storage::disk('public')->assertExists($old);
    }

    public function test_deleting_a_photo_removes_row_and_file(): void
    {
        $path = $this->stored('hapus');
        $image = ActivityImage::factory()->for($this->activity, 'activity')->create(['image_path' => $path]);

        $this->delete($this->url("/images/{$image->id}"))->assertRedirect($this->url())->assertSessionHas('success');

        $this->assertModelMissing($image);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_deleting_keeps_a_file_that_is_still_used_elsewhere(): void
    {
        $shared = $this->stored('bersama');
        $image = ActivityImage::factory()->for($this->activity, 'activity')->create(['image_path' => $shared]);
        BusinessActivity::factory()->create(['image_path' => $shared]);

        $this->delete($this->url("/images/{$image->id}"));

        $this->assertModelMissing($image);
        Storage::disk('public')->assertExists($shared);
    }

    public function test_deleting_a_placeholder_photo_works(): void
    {
        $image = ActivityImage::factory()->for($this->activity, 'activity')->create(['image_path' => 'https://images.unsplash.com/y.jpg']);

        $this->delete($this->url("/images/{$image->id}"))->assertSessionHas('success');

        $this->assertModelMissing($image);
    }

    public function test_photo_from_another_activity_cannot_be_touched(): void
    {
        $otherImage = ActivityImage::factory()->create(['caption' => 'milik lain']);

        $this->patch($this->url("/images/{$otherImage->id}"), ['caption' => 'dibajak'])->assertNotFound();
        $this->delete($this->url("/images/{$otherImage->id}"))->assertNotFound();

        $this->assertSame('milik lain', $otherImage->refresh()->caption);
    }

    public function test_unknown_activity_returns_404(): void
    {
        $this->get('/admin/gallery/99999')->assertNotFound();
        $this->post('/admin/gallery/99999/images', ['images' => [$this->photo()]])->assertNotFound();
    }

    public function test_reorder_updates_sort_order(): void
    {
        [$a, $b, $c] = ActivityImage::factory()->count(3)->for($this->activity, 'activity')
            ->sequence(['sort_order' => 1], ['sort_order' => 2], ['sort_order' => 3])->create()->all();

        $this->put($this->url('/order'), ['order' => [$c->id, $a->id, $b->id]])
            ->assertRedirect($this->url())
            ->assertSessionHas('success');

        $this->assertSame([$c->id, $a->id, $b->id], $this->activity->images()->pluck('id')->all());
        $this->assertSame([1, 2, 3], $this->activity->images()->pluck('sort_order')->all());
    }

    public function test_reorder_rejects_missing_foreign_or_duplicate_ids(): void
    {
        [$a, $b] = ActivityImage::factory()->count(2)->for($this->activity, 'activity')
            ->sequence(['sort_order' => 1], ['sort_order' => 2])->create()->all();
        $foreign = ActivityImage::factory()->create(['sort_order' => 9]);

        $this->put($this->url('/order'), ['order' => [$a->id]])->assertSessionHasErrors('order');
        $this->put($this->url('/order'), ['order' => [$a->id, $foreign->id]])->assertSessionHasErrors('order');
        $this->put($this->url('/order'), ['order' => [$a->id, $a->id]])->assertSessionHasErrors('order.1');
        $this->put($this->url('/order'), [])->assertSessionHasErrors('order');

        $this->assertSame([1, 2], [$a->refresh()->sort_order, $b->refresh()->sort_order]);
        $this->assertSame(9, $foreign->refresh()->sort_order);
    }

    public function test_equal_sort_orders_have_a_stable_order_by_id(): void
    {
        $first = ActivityImage::factory()->for($this->activity, 'activity')->create(['sort_order' => 1]);
        $second = ActivityImage::factory()->for($this->activity, 'activity')->create(['sort_order' => 1]);

        $this->assertSame([$first->id, $second->id], $this->activity->images()->pluck('id')->all());
    }

    public function test_uploaded_photos_appear_on_the_public_detail_page_in_order(): void
    {
        $this->post($this->url('/images'), ['images' => [$this->photo('a.jpg'), $this->photo('b.jpg')]]);
        [$first, $second] = $this->activity->images()->pluck('image_path')->all();

        auth()->logout();

        $this->get('/layanan-jasa/kat-galeri/akt-galeri')
            ->assertOk()
            ->assertSeeInOrder(['/storage/'.$first, '/storage/'.$second], false);
    }

    public function test_public_page_renders_for_zero_one_six_and_seven_photos(): void
    {
        auth()->logout();

        foreach ([0, 1, 6, 7] as $count) {
            $activity = BusinessActivity::factory()->for($this->activity->category, 'category')->create(['slug' => "galeri-{$count}"]);
            ActivityImage::factory()->count($count)->for($activity, 'activity')->create();

            $this->get("/layanan-jasa/kat-galeri/galeri-{$count}")
                ->assertOk()
                ->assertSee("{$count} / 6", false);
        }
    }

    public function test_manage_pages_have_a_plain_back_link_to_their_list(): void
    {
        $category = $this->activity->category;

        $pages = [
            '/admin/categories/create' => route('admin.categories.index'),
            "/admin/categories/{$category->id}/edit" => route('admin.categories.index'),
            '/admin/activities/create' => route('admin.activities.index'),
            "/admin/activities/{$this->activity->id}/edit" => route('admin.activities.index', ['category' => $category->id]),
            $this->url() => route('admin.gallery.index', ['category' => $category->id]),
            '/admin/profil' => route('admin.dashboard'),
        ];

        foreach ($pages as $url => $target) {
            $html = $this->get($url)->assertOk()->getContent();

            // Label hanya "Kembali", tanpa nama menu; tujuan tautan tetap benar.
            $this->assertMatchesRegularExpression('#<a\s+href="'.preg_quote(e($target), '#').'"[^>]*>.*?</span>\s*Kembali\s*</a>#s', $html, "Tombol kembali di {$url}");
            $this->assertStringNotContainsString('Kembali ke ', $html, "Label panjang masih ada di {$url}");
        }
    }

    public function test_gallery_photos_can_be_previewed_with_the_shared_alpine_lightbox(): void
    {
        $a = ActivityImage::factory()->for($this->activity, 'activity')->create(['image_path' => 'https://example.com/a.jpg', 'sort_order' => 1]);
        $b = ActivityImage::factory()->for($this->activity, 'activity')->create(['image_path' => 'https://example.com/b.jpg', 'sort_order' => 2]);

        $this->get($this->url())
            ->assertOk()
            // komponen lightbox yang sama dengan halaman publik
            ->assertSee('documentationLightbox(', false)
            ->assertSee('@documentation-open.window', false)
            // daftar awal berurutan sesuai urutan tersimpan
            ->assertSeeInOrder(['https://example.com/a.jpg', 'https://example.com/b.jpg'], false)
            // klik foto dan tombol akses papan ketik membuka pratinjau
            ->assertSee("openPhoto({$a->id})", false)
            ->assertSee("openPhoto({$b->id})", false)
            ->assertSee('aria-label="Lihat foto 1"', false);
    }

    public function test_photo_card_is_compact_with_icon_actions_only(): void
    {
        $image = ActivityImage::factory()->for($this->activity, 'activity')->create(['image_path' => 'https://example.com/a.jpg']);

        $this->get($this->url())
            ->assertOk()
            // satu ikon pembesar saja (overlay hover), tombol pojok kiri bawah sudah dihapus
            ->assertDontSee('absolute bottom-2 left-2', false)
            // keyboard: foto bisa difokus dan dibuka dengan Enter/Spasi
            ->assertSee('role="button" tabindex="0"', false)
            // aksi berupa ikon: ganti (otomatis kirim), simpan caption, hapus lewat form terpisah
            ->assertSee('name="image"', false)
            ->assertSee('requestSubmit()', false)
            ->assertSee('title="Simpan caption"', false)
            ->assertSee('form="delete-'.$image->id.'"', false)
            ->assertSee('id="delete-'.$image->id.'"', false)
            // label panjang lama tidak ada lagi
            ->assertDontSee('Simpan perubahan')
            ->assertDontSee('Hapus foto</button>', false)
            ->assertDontSee('Ganti foto (opsional)');
    }

    public function test_gallery_without_photos_does_not_render_the_lightbox(): void
    {
        $this->get($this->url())->assertOk()->assertDontSee('documentationLightbox(', false);
    }

    public function test_activity_index_links_to_gallery_and_sidebar_has_gallery_menu(): void
    {
        $this->get('/admin/activities')->assertOk()->assertSee(route('admin.gallery.show', $this->activity), false);
        $this->get('/admin')->assertOk()->assertSee(route('admin.gallery.index'), false)->assertSee('Galeri');
    }
}
