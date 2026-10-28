<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\PageContents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LastChangedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->create(['name' => 'Admin Uji']));
    }

    private function logFor(string $subject, string $action, string $at, string $who = 'Admin Uji'): void
    {
        ActivityLog::forceCreate([
            'user_name' => $who, 'action' => $action, 'menu' => 'Kategori', 'subject' => $subject,
            'detail' => 'Uji', 'created_at' => $at,
        ]);
    }

    public function test_saving_a_category_records_who_and_the_edit_page_shows_it(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Kat Info', 'slug' => 'kat-info']);

        $this->put("/admin/categories/{$category->id}", ['name' => 'Kat Info Baru', 'slug' => 'kat-info', 'description' => 'x', 'is_published' => 1]);

        $log = ActivityLog::where('action', 'updated')->sole();
        $this->assertSame("category:{$category->id}", $log->subject);

        $html = $this->get("/admin/categories/{$category->id}/edit")->assertOk()->getContent();
        $this->assertStringContainsString('data-last-changed', $html);
        $this->assertStringContainsString('Terakhir diubah', $html);
        $this->assertMatchesRegularExpression('#oleh\s*<span[^>]*>Admin Uji</span>#', $html);
        $this->assertStringContainsString('yang lalu', $html);
    }

    public function test_a_freshly_created_category_says_created_by_and_a_later_edit_replaces_it(): void
    {
        $this->post('/admin/categories', ['name' => 'Kat Baru Info', 'slug' => 'kat-baru-info', 'description' => 'x', 'is_published' => 1])
            ->assertRedirect();
        $category = ServiceCategory::where('name', 'Kat Baru Info')->sole();

        $this->assertSame("category:{$category->id}", ActivityLog::where('action', 'created')->sole()->subject);

        $html = $this->get("/admin/categories/{$category->id}/edit")->getContent();
        $this->assertMatchesRegularExpression('#Dibuat\s*oleh#', $html);
        $this->assertStringNotContainsString('Terakhir diubah', $html);

        $this->put("/admin/categories/{$category->id}", ['name' => 'Kat Baru Info 2', 'slug' => 'kat-baru-info', 'description' => 'x', 'is_published' => 1]);

        $html = $this->get("/admin/categories/{$category->id}/edit")->getContent();
        $this->assertStringContainsString('Terakhir diubah', $html);
        $this->assertStringNotContainsString('Dibuat', substr($html, strpos($html, 'data-last-changed'), 300));
    }

    public function test_the_activity_edit_page_shows_the_last_change_of_that_activity_only(): void
    {
        $category = ServiceCategory::factory()->create();
        $mine = BusinessActivity::factory()->for($category, 'category')->create(['name' => 'Akt Saya']);
        $other = BusinessActivity::factory()->for($category, 'category')->create(['name' => 'Akt Lain']);

        $this->logFor("activity:{$other->id}", 'updated', '2026-10-27 10:00:00', 'Admin Lain');
        $this->logFor("activity:{$mine->id}", 'updated', '2026-10-20 08:00:00', 'Admin Saya');

        $html = $this->get("/admin/activities/{$mine->id}/edit")->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#oleh\s*<span[^>]*>Admin Saya</span>#', $html);
        $this->assertStringNotContainsString('Admin Lain', $html);
        // Tooltip memuat tanggal lengkap dalam bahasa Indonesia.
        $this->assertStringContainsString('title="20 Okt 2026, 08:00"', $html);
    }

    public function test_the_latest_change_wins_and_non_change_actions_are_ignored(): void
    {
        $category = ServiceCategory::factory()->create();
        $this->logFor("category:{$category->id}", 'created', '2026-10-01 08:00:00', 'Pembuat');
        $this->logFor("category:{$category->id}", 'updated', '2026-10-05 08:00:00', 'Pengubah Lama');
        $this->logFor("category:{$category->id}", 'updated', '2026-10-09 08:00:00', 'Pengubah Baru');
        $this->logFor("category:{$category->id}", 'reordered', '2026-10-12 08:00:00', 'Pengurut');

        $this->assertSame('Pengubah Baru', ActivityLog::lastChangeFor($category)->user_name);

        $this->get("/admin/categories/{$category->id}/edit")->assertSee('Pengubah Baru')->assertDontSee('Pengurut')->assertDontSee('Pengubah Lama');
    }

    public function test_without_any_log_the_model_time_is_used_without_a_name(): void
    {
        $category = ServiceCategory::factory()->create();
        ServiceCategory::whereKey($category->id)->update(['created_at' => now()->subDays(10), 'updated_at' => now()->subDays(3)]);

        $html = $this->get("/admin/categories/{$category->id}/edit")->getContent();

        $this->assertStringContainsString('Terakhir diubah', $html);
        $this->assertStringContainsString('3 hari yang lalu', $html);
        $this->assertStringNotContainsString('oleh', substr($html, strpos($html, 'data-last-changed'), 400));

        // Waktu ubah sama dengan waktu buat: "Dibuat".
        ServiceCategory::whereKey($category->id)->update(['created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)]);
        $this->assertMatchesRegularExpression('#Dibuat\s*&middot;|Dibuat\s*·#u', html_entity_decode($this->get("/admin/categories/{$category->id}/edit")->getContent()) ?: '');
    }

    public function test_the_page_editor_shows_never_changed_then_who_changed_the_content(): void
    {
        $this->get('/admin/pages/about')->assertOk()->assertSee('Belum pernah diubah (memakai teks bawaan)');

        $payload = ['content' => []];
        foreach (PageContents::fields('about') as $key => $field) {
            if (($field['type'] ?? 'text') !== 'image') {
                $payload['content'][$key] = $field['default'];
            }
        }
        $payload['content']['hero_label'] = 'Profil Uji Info';

        $this->put('/admin/pages/about', $payload)->assertSessionHasNoErrors();

        $log = ActivityLog::where('menu', 'Konten Halaman')->where('action', 'updated')->sole();
        $this->assertSame('page:about', $log->subject);

        $html = $this->get('/admin/pages/about')->getContent();
        $this->assertStringContainsString('Terakhir diubah', $html);
        $this->assertMatchesRegularExpression('#oleh\s*<span[^>]*>Admin Uji</span>#', $html);

        // Halaman lain tidak ikut.
        $this->get('/admin/pages/home')->assertSee('Belum pernah diubah (memakai teks bawaan)');
    }

    public function test_setting_or_clearing_the_default_is_not_a_content_change_but_a_full_reset_is(): void
    {
        $this->post('/admin/pages/about/default')->assertSessionHas('success');
        $this->assertNull(ActivityLog::where('action', 'default')->sole()->subject);

        $this->delete('/admin/pages/about')->assertSessionHas('success');
        $reset = ActivityLog::where('action', 'reset')->where('detail', 'like', 'Mengembalikan seluruh konten%')->sole();
        $this->assertSame('page:about', $reset->subject);
    }

    public function test_the_logger_accepts_models_and_text_and_stores_a_key(): void
    {
        $category = ServiceCategory::factory()->create();
        $activity = BusinessActivity::factory()->for($category, 'category')->create();

        ActivityLogger::record('updated', 'Kategori', 'a', subject: $category);
        ActivityLogger::record('updated', 'Aktivitas Usaha', 'b', subject: $activity);
        ActivityLogger::record('updated', 'Konten Halaman', 'c', subject: 'page:home');
        ActivityLogger::record('updated', 'Kategori', 'd');

        $this->assertSame(
            ["category:{$category->id}", "activity:{$activity->id}", 'page:home', null],
            ActivityLog::orderBy('id')->pluck('subject')->all(),
        );
    }

    public function test_deleting_an_item_keeps_the_log_working_and_the_edit_page_of_others_unaffected(): void
    {
        $gone = ServiceCategory::factory()->create(['name' => 'Akan Hilang']);
        $stay = ServiceCategory::factory()->create(['name' => 'Tetap Ada']);
        $this->logFor("category:{$gone->id}", 'updated', '2026-10-01 08:00:00');

        $this->delete("/admin/categories/{$gone->id}")->assertRedirect();

        $this->get("/admin/categories/{$stay->id}/edit")->assertOk()->assertSee('Tetap Ada');
        $this->get('/admin/activity-log?jenis=semua')->assertOk();
    }

    public function test_the_migration_fills_old_logs_by_unique_name_and_leaves_ambiguous_ones(): void
    {
        $unique = ServiceCategory::factory()->create(['name' => 'Kategori Unik']);
        ServiceCategory::factory()->create(['name' => 'Kembar']);
        ServiceCategory::factory()->create(['name' => 'Kembar', 'slug' => 'kembar-dua']);
        $category = ServiceCategory::factory()->create(['name' => 'Induk']);
        $activity = BusinessActivity::factory()->for($category, 'category')->create(['name' => 'Aktivitas "Berkutip"']);

        $rows = [
            ['Kategori', 'created', 'Menambahkan kategori "Kategori Unik" (status: Terbit)', "category:{$unique->id}"],
            ['Kategori', 'updated', 'Mengubah kategori "Kategori Unik" (yang diubah: Nama)', "category:{$unique->id}"],
            ['Kategori', 'updated', 'Mengubah kategori "Kembar" (yang diubah: Nama)', null],
            ['Kategori', 'updated', 'Mengubah kategori "Sudah Dihapus" (yang diubah: Nama)', null],
            ['Kategori', 'deleted', 'Menghapus kategori "Kategori Unik"', null],
            ['Aktivitas Usaha', 'updated', 'Mengubah aktivitas "Aktivitas "Berkutip"" (yang diubah: Nama)', "activity:{$activity->id}"],
            ['Konten Halaman', 'updated', 'Mengubah konten halaman Tentang Kami (bagian yang diubah: Bagian utama)', 'page:about'],
            ['Konten Halaman', 'reset', 'Mengembalikan seluruh konten halaman Beranda ke setelan default bawaan sistem', 'page:home'],
            ['Konten Halaman', 'reset', 'Menghapus setelan default yang ditetapkan admin untuk halaman Beranda (kembali ke default bawaan sistem)', null],
            ['Konten Halaman', 'updated', 'Mengubah konten halaman Tidak Dikenal (bagian yang diubah: X)', null],
        ];

        $ids = [];
        foreach ($rows as [$menu, $action, $detail]) {
            $ids[] = ActivityLog::forceCreate(['user_name' => 'A', 'action' => $action, 'menu' => $menu, 'detail' => $detail, 'created_at' => now()])->id;
        }

        // Jalankan ulang bagian isi (kolom sudah ada di database uji): cukup memanggil logika pengisian.
        $migration = require database_path('migrations/2026_10_29_000000_add_subject_to_activity_logs.php');
        $method = new \ReflectionMethod($migration, 'backfillNamed');
        $method->invoke($migration, 'Kategori', 'kategori', 'category', ServiceCategory::query()->pluck('name', 'id')->all());
        $method->invoke($migration, 'Aktivitas Usaha', 'aktivitas', 'activity', BusinessActivity::query()->pluck('name', 'id')->all());
        (new \ReflectionMethod($migration, 'backfillPages'))->invoke($migration);

        foreach ($rows as $index => [, , , $expected]) {
            $this->assertSame($expected, ActivityLog::find($ids[$index])->subject, $rows[$index][2]);
        }

        $this->assertTrue(Schema::hasColumn('activity_logs', 'subject'));
    }
}
