<?php

namespace Tests\Feature\Admin;

use App\Models\PageContent;
use App\Models\User;
use App\Support\PageContents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageContentTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function defaults(): array
    {
        return array_map(fn ($field) => $field['default'], PageContents::fields('home'));
    }

    public function test_guests_cannot_open_or_change_page_content(): void
    {
        $this->get('/admin/pages')->assertRedirect('/login');
        $this->get('/admin/pages/home')->assertRedirect('/login');
        $this->put('/admin/pages/home', ['content' => $this->defaults()])->assertRedirect('/login');
        $this->delete('/admin/pages/home')->assertRedirect('/login');
    }

    public function test_admin_sees_the_list_and_the_form_with_current_texts(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/pages')->assertOk()->assertSee('Beranda');
        $this->get('/admin/pages/home')
            ->assertOk()
            ->assertSee('name="content[hero_title]"', false)
            ->assertSee('Solusi');
        $this->get('/admin/pages/tidak-ada')->assertNotFound();
    }

    public function test_homepage_shows_default_texts_until_the_admin_changes_them(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Produk dan')
            ->assertSee('Beragam bidang.')
            ->assertSee('Mari berdiskusi.');
    }

    public function test_admin_changes_show_up_on_the_homepage_without_touching_the_layout(): void
    {
        $normalize = fn (string $html) => preg_replace('/\s+/', ' ', preg_replace('/>\s+</', '><', $html));
        $before = $normalize($this->get('/')->getContent());

        $this->actingAs(User::factory()->create());
        $this->put('/admin/pages/home', ['content' => array_merge($this->defaults(), [
            'hero_title' => 'Produk dan *Kemitraan* untuk Berbagai Kebutuhan Bisnis.',
            'hero_button_primary' => 'Kenali Kami',
            'intro_stat_number' => '20',
        ])])->assertRedirect('/admin/pages/home')->assertSessionHas('success');

        auth()->logout();
        $after = $normalize($this->get('/')->getContent());

        $this->assertStringContainsString('Kemitraan', $after);
        $this->assertStringContainsString('Kenali Kami', $after);
        $this->assertStringNotContainsString('>Solusi<', $after);

        // Hanya teks yang berubah: setelah ditukar kembali, markup harus identik.
        $restored = str_replace(['Kemitraan', 'Kenali Kami'], ['Solusi', 'Tentang Kami'], $after);
        $restored = preg_replace('/(<p[^>]*>) ?20 ?(<\/p>)/', '${1} 14 ${2}', $restored);
        $this->assertSame($normalize($before), $normalize($restored));
    }

    public function test_text_is_escaped_so_admin_cannot_inject_markup(): void
    {
        $this->actingAs(User::factory()->create());
        $this->put('/admin/pages/home', ['content' => array_merge($this->defaults(), [
            'hero_title' => '<script>alert(1)</script> *x*',
        ])])->assertSessionHasNoErrors();

        auth()->logout();
        $this->get('/')->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;', false);
    }

    public function test_required_and_length_limits_are_enforced_with_indonesian_messages(): void
    {
        $this->actingAs(User::factory()->create());

        $this->from('/admin/pages/home')
            ->put('/admin/pages/home', ['content' => array_merge($this->defaults(), [
                'hero_title' => '',
                'hero_eyebrow' => str_repeat('a', 81),
            ])])
            ->assertRedirect('/admin/pages/home')
            ->assertSessionHasErrors(['content.hero_title', 'content.hero_eyebrow']);

        $this->assertSame(0, PageContent::count());
        $this->followingRedirects()->from('/admin/pages/home')->put('/admin/pages/home', ['content' => array_merge($this->defaults(), ['hero_title' => ''])])->assertSee('wajib diisi');
    }

    public function test_values_equal_to_the_default_are_not_stored_and_unknown_keys_are_ignored(): void
    {
        $this->actingAs(User::factory()->create());

        $this->put('/admin/pages/home', ['content' => array_merge($this->defaults(), ['kunci_asing' => 'x'])])
            ->assertSessionHasNoErrors();

        $this->assertSame(0, PageContent::count());
    }

    public function test_reset_restores_all_default_texts(): void
    {
        $this->actingAs(User::factory()->create());
        $this->put('/admin/pages/home', ['content' => array_merge($this->defaults(), ['hero_title' => 'Produk dan *Kemitraan* untuk Berbagai Kebutuhan Bisnis.'])]);
        $this->assertSame(1, PageContent::count());

        $this->delete('/admin/pages/home')->assertRedirect('/admin/pages/home');

        $this->assertSame(0, PageContent::count());
        auth()->logout();
        $this->get('/')->assertSee('Solusi')->assertDontSee('Kemitraan');
    }

    public function test_sidebar_links_to_page_content(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin')->assertSee('Konten Halaman')->assertSee(route('admin.pages.index'), false);
    }
}
