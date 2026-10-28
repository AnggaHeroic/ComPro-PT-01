<?php

namespace Tests\Feature;

use App\Support\ErrorLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Halaman error khusus hanya tampil saat debug mati (seperti di produksi).
        config(['app.debug' => false]);

        Route::middleware('web')->group(function () {
            Route::get('/uji-error-500', fn () => throw new RuntimeException('rahasia internal'));
            Route::get('/uji-error-419', fn () => abort(419));
        });
    }

    public function test_the_500_page_uses_the_custom_design_and_hides_the_error_details(): void
    {
        $this->get('/uji-error-500')
            ->assertStatus(500)
            ->assertSee('Error 500')
            ->assertSee('Terjadi kesalahan')
            ->assertSee('pada server')
            ->assertSee('Kembali ke Beranda')
            ->assertSee('Coba Lagi')
            ->assertSee('viewBox="0 0 760 320"', false)
            ->assertDontSee('rahasia internal')
            ->assertDontSee('RuntimeException');
    }

    public function test_the_419_page_explains_the_expired_session_and_links_back(): void
    {
        $this->from('/login')->get('/uji-error-419')
            ->assertStatus(419)
            ->assertSee('Error 419')
            ->assertSee('Sesi halaman')
            ->assertSee('berakhir.')
            ->assertSee('Muat Ulang Halaman')
            ->assertSee('href="'.url('/login').'"', false)
            ->assertSee('Kembali ke Beranda');
    }

    public function test_the_419_page_without_a_previous_page_links_to_home(): void
    {
        $this->get('/uji-error-419')->assertStatus(419)->assertSee('href="'.url('/').'"', false);
    }

    public function test_error_pages_keep_the_main_layout_with_navbar_and_footer(): void
    {
        $this->assertSame('layouts.app', ErrorLayout::name());

        $html = $this->get('/uji-error-500')->getContent();
        $this->assertStringContainsString('<footer', $html);
    }

    public function test_the_500_page_still_renders_when_the_database_itself_is_the_problem(): void
    {
        Schema::drop('page_contents');
        Schema::drop('page_content_defaults');

        $this->assertSame('layouts.error', ErrorLayout::name());

        $this->get('/uji-error-500')
            ->assertStatus(500)
            ->assertSee('Error 500')
            ->assertSee('Terjadi kesalahan')
            ->assertDontSee('<footer', false);
    }

    public function test_the_404_page_is_unchanged(): void
    {
        $this->get('/halaman-yang-tidak-ada')->assertStatus(404)->assertSee('Error 404')->assertSee('tidak ditemukan.');
    }

    public function test_the_error_pages_use_the_same_file_names_as_the_404_page(): void
    {
        foreach ([404, 419, 500] as $code) {
            $this->assertFileExists(resource_path("views/errors/{$code}.blade.php"));
        }
    }
}
