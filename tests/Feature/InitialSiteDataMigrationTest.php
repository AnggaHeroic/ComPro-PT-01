<?php

namespace Tests\Feature;

use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class InitialSiteDataMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function runMigration(string $environment = 'production'): void
    {
        $original = app()->environment();
        app()['env'] = $environment;

        try {
            (require database_path('migrations/2026_10_27_000000_seed_initial_site_data.php'))->up();
        } finally {
            app()['env'] = $original;
        }
    }

    private function adminAccount(): void
    {
        config(['admin.account' => ['name' => 'Admin Situs', 'email' => 'admin@example.test', 'password' => 'rahasia-1234']]);
    }

    public function test_migrating_a_fresh_database_creates_the_admin_and_the_default_catalog(): void
    {
        $this->adminAccount();

        $this->runMigration();

        $admin = User::sole();
        $this->assertSame('admin@example.test', $admin->email);
        $this->assertSame('Admin Situs', $admin->name);
        $this->assertTrue(Hash::check('rahasia-1234', $admin->password));
        $this->assertSame(5, ServiceCategory::count());
        $this->assertSame(14, BusinessActivity::count());
        $this->assertSame(5, ServiceCategory::where('is_published', true)->count());
    }

    public function test_a_database_that_already_has_data_is_left_untouched(): void
    {
        $this->adminAccount();
        $user = User::factory()->create(['email' => 'lama@example.test', 'password' => Hash::make('sandi-lama')]);
        $category = ServiceCategory::factory()->create(['name' => 'Kategori Admin']);

        $this->runMigration();

        $this->assertSame(1, User::count());
        $this->assertTrue(Hash::check('sandi-lama', $user->fresh()->password));
        $this->assertSame(['Kategori Admin'], ServiceCategory::pluck('name')->all());
        $this->assertSame(0, BusinessActivity::count());
        $this->assertNotNull($category->fresh());
    }

    public function test_running_it_twice_does_not_duplicate_anything(): void
    {
        $this->adminAccount();

        $this->runMigration();
        $this->runMigration();

        $this->assertSame(1, User::count());
        $this->assertSame(5, ServiceCategory::count());
        $this->assertSame(14, BusinessActivity::count());
    }

    public function test_it_stops_with_a_clear_message_when_the_admin_login_is_missing_from_env(): void
    {
        config(['admin.account' => ['name' => 'Admin', 'email' => null, 'password' => null]]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ADMIN_EMAIL dan ADMIN_PASSWORD');

        $this->runMigration();
    }

    public function test_it_does_nothing_in_the_testing_environment(): void
    {
        $this->adminAccount();

        $this->runMigration('testing');

        $this->assertSame(0, User::count());
        $this->assertSame(0, ServiceCategory::count());
    }
}
