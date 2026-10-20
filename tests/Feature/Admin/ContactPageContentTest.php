<?php

namespace Tests\Feature\Admin;

use App\Models\PageContent;
use App\Models\User;
use App\Support\PageContents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContactPageContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->create());
    }

    /** @return array<string, array<string, string>> */
    private function texts(array $overrides = []): array
    {
        $texts = [];
        foreach (PageContents::fields('contact') as $key => $field) {
            if (($field['type'] ?? 'text') !== 'image') {
                $texts[$key] = $field['default'];
            }
        }

        return ['content' => array_merge($texts, $overrides)];
    }

    public function test_contact_page_is_listed_and_the_public_page_shows_defaults(): void
    {
        $this->get('/admin/pages')->assertSee('Kontak Kami')->assertDontSee('akan ditambahkan');
        $this->get('/admin/pages/contact')
            ->assertOk()
            ->assertSee('name="content[contact_email]"', false)
            ->assertSee('name="content[contact_phone]"', false)
            ->assertSee('name="image_hero_image"', false);

        auth()->logout();
        $this->get('/kontak-kami')
            ->assertOk()
            ->assertSee('sputramaju@gmail.com')
            ->assertSee('href="mailto:sputramaju@gmail.com"', false)
            ->assertSee('href="tel:081130521216"', false)
            ->assertSee('Jalan Tukad Batanghari Nomor 6, Panjer, Denpasar Selatan, Kota Denpasar.')
            ->assertSee('Kami siap membantu Anda.')
            ->assertSee('Kirim Email')
            ->assertSee('Interior ruang kantor modern');
    }

    public function test_contact_details_drive_links_map_and_the_homepage_contact_block(): void
    {
        $this->put('/admin/pages/contact', $this->texts([
            'contact_email' => 'halo@contoh.co.id',
            'contact_phone' => '+62 361 (123) 456',
            'contact_address' => 'Jl. Sunset Road No. 10, Kuta, Badung',
        ]))->assertSessionHasNoErrors();

        auth()->logout();
        $html = $this->get('/kontak-kami')->getContent();
        $this->assertStringContainsString('href="mailto:halo@contoh.co.id"', $html);
        $this->assertStringContainsString('href="tel:+62361123456"', $html);
        $this->assertStringContainsString('+62 361 (123) 456', $html);
        $this->assertStringContainsString('query='.rawurlencode('Jl. Sunset Road No. 10, Kuta, Badung'), $html);
        $this->assertStringContainsString('q='.rawurlencode('Jl. Sunset Road No. 10, Kuta, Badung').'&amp;output=embed', $html);
        // Footer belum terhubung ke data ini (tahap footer), jadi nilai lama masih ada di sana.

        // Bagian kontak di Beranda memakai data yang sama.
        $home = $this->get('/')->getContent();
        $this->assertStringContainsString('href="mailto:halo@contoh.co.id"', $home);
        $this->assertStringContainsString('href="tel:+62361123456"', $home);
        $this->assertStringContainsString('Jl. Sunset Road No. 10, Kuta, Badung', $home);
    }

    public function test_map_keyword_overrides_the_address_for_the_map_only(): void
    {
        $this->put('/admin/pages/contact', $this->texts(['location_map_query' => 'Kantor PT Putra Maju Sukses Denpasar']))
            ->assertSessionHasNoErrors();

        auth()->logout();
        $html = $this->get('/kontak-kami')->getContent();
        $this->assertStringContainsString('q='.rawurlencode('Kantor PT Putra Maju Sukses Denpasar'), $html);
        // Tautan alamat tetap memakai alamat.
        $this->assertStringContainsString('query='.rawurlencode('Jalan Tukad Batanghari Nomor 6, Panjer, Denpasar Selatan, Kota Denpasar'), $html);
    }

    public function test_email_and_phone_are_validated(): void
    {
        foreach ([
            ['contact_email', 'bukan-email'],
            ['contact_phone', 'telepon-abc'],
            ['contact_phone', '<script>1</script>'],
            ['contact_address', ''],
        ] as [$key, $value]) {
            $this->put('/admin/pages/contact', $this->texts([$key => $value]))
                ->assertSessionHasErrors("content.{$key}");
        }

        $this->assertSame(0, PageContent::count());
    }

    public function test_title_hero_image_and_texts_are_editable_and_escaped(): void
    {
        $this->put('/admin/pages/contact', $this->texts([
            'hero_title' => 'Hubungi *Kami* <b>sekarang</b>',
            'cta_button' => 'Tulis Email',
        ]) + ['image_hero_image' => UploadedFile::fake()->image('kantor.jpg', 2400, 1200)])->assertSessionHasNoErrors();

        $path = PageContent::where(['page' => 'contact', 'key' => 'hero_image'])->value('value');
        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);

        auth()->logout();
        $html = $this->get('/kontak-kami')->getContent();
        $this->assertStringContainsString('<span class="text-primary">Kami</span>', $html);
        $this->assertStringContainsString('&lt;b&gt;sekarang&lt;/b&gt;', $html);
        $this->assertStringContainsString('Tulis Email', $html);
        // Foto yang sama dipakai di semua tempat (latar desktop, foto mobile, latar bagian bawah).
        $this->assertSame(3, substr_count($html, Storage::disk('public')->url($path)));
    }

    public function test_reset_restores_defaults_and_removes_the_uploaded_photo(): void
    {
        $this->put('/admin/pages/contact', $this->texts(['contact_phone' => '0361 111 222']) + [
            'image_hero_image' => UploadedFile::fake()->image('a.jpg'),
        ]);
        $path = PageContent::where('key', 'hero_image')->value('value');

        $this->delete('/admin/pages/contact')->assertRedirect('/admin/pages/contact');

        $this->assertSame(0, PageContent::count());
        Storage::disk('public')->assertMissing($path);
        auth()->logout();
        $this->get('/kontak-kami')->assertSee('tel:081130521216', false);
    }

    public function test_contact_markup_does_not_change_when_only_texts_are_edited(): void
    {
        $normalize = fn (string $html) => preg_replace('/\s+/', ' ', preg_replace('/>\s+</', '><', $html));
        auth()->logout();
        $before = $normalize($this->get('/kontak-kami')->getContent());

        $this->actingAs(User::first());
        $this->put('/admin/pages/contact', $this->texts(['cta_title' => 'Hubungi Tim Kami', 'info_title' => 'Kontak Resmi']));

        auth()->logout();
        $after = $normalize($this->get('/kontak-kami')->getContent());

        $this->assertSame($before, str_replace(['Hubungi Tim Kami', 'Kontak Resmi'], ['Kami siap membantu Anda.', 'Terhubung dengan kami.'], $after));
    }
}
