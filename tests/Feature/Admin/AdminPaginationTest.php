<?php

namespace Tests\Feature\Admin;

use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_every_admin_list_uses_the_admin_pagination_design_in_indonesian(): void
    {
        $category = ServiceCategory::factory()->create();
        ServiceCategory::factory()->count(16)->create();
        BusinessActivity::factory()->count(16)->for($category, 'category')->create();

        foreach ([
            '/admin/categories' => 'kategori',
            '/admin/activities' => 'aktivitas',
            '/admin/gallery' => 'aktivitas',
        ] as $url => $label) {
            $this->get($url)->assertOk()
                ->assertSee('Menampilkan')->assertSee($label)
                ->assertDontSee('Showing')->assertDontSee('results')
                ->assertSee('aria-label="Navigasi halaman"', false)
                ->assertSee('aria-label="Halaman berikutnya"', false);
        }
    }
}
