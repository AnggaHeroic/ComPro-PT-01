<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityImage;
use App\Models\ActivityLog;
use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\PageContents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->admin = User::factory()->create(['name' => 'Admin Uji']);
        $this->actingAs($this->admin);
    }

    private function last(): ?ActivityLog
    {
        return ActivityLog::latest('id')->first();
    }

    public function test_guests_cannot_open_the_activity_log_and_it_is_in_the_sidebar(): void
    {
        $this->get('/admin/activity-log')->assertOk()->assertSee('Riwayat Aktivitas');
        $this->get('/admin')->assertSee('href="'.route('admin.activity-log.index').'"', false);

        auth()->logout();
        $this->get('/admin/activity-log')->assertRedirect('/login');
    }

    public function test_category_create_update_and_delete_are_logged_with_details(): void
    {
        $this->post('/admin/categories', ['name' => 'Kategori Baru', 'description' => 'x', 'is_published' => 1]);
        $log = $this->last();
        $this->assertSame(['created', 'Kategori', 'Admin Uji'], [$log->action, $log->menu, $log->user_name]);
        $this->assertSame($this->admin->id, $log->user_id);
        $this->assertStringContainsString('"Kategori Baru" ditambahkan', $log->detail);
        $this->assertStringContainsString('Terbit', $log->detail);

        $category = ServiceCategory::where('name', 'Kategori Baru')->first();
        $this->put("/admin/categories/{$category->id}", ['name' => 'Kategori Diubah', 'slug' => $category->slug, 'description' => 'x', 'is_published' => 1]);
        $log = $this->last();
        $this->assertSame('updated', $log->action);
        $this->assertStringContainsString('"Kategori Diubah" — mengubah: Nama', $log->detail);
        $this->assertStringNotContainsString('Deskripsi', $log->detail);

        // Tanpa perubahan apa pun tidak ada catatan baru.
        $count = ActivityLog::count();
        $this->put("/admin/categories/{$category->id}", ['name' => 'Kategori Diubah', 'slug' => $category->slug, 'description' => 'x', 'is_published' => 1]);
        $this->assertSame($count, ActivityLog::count());

        $this->delete("/admin/categories/{$category->id}");
        $log = $this->last();
        $this->assertSame(['deleted', 'Kategori'], [$log->action, $log->menu]);
        $this->assertStringContainsString('"Kategori Diubah" dihapus', $log->detail);
    }

    public function test_a_blocked_category_deletion_is_not_logged(): void
    {
        $category = ServiceCategory::factory()->create();
        BusinessActivity::factory()->for($category, 'category')->create();
        $before = ActivityLog::count();

        $this->delete("/admin/categories/{$category->id}")->assertSessionHas('error');

        $this->assertSame($before, ActivityLog::count());
    }

    public function test_activity_gallery_and_reorder_actions_are_logged(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Kat X']);
        $activity = BusinessActivity::factory()->for($category, 'category')->create(['name' => 'Akt Y']);

        $this->put("/admin/activities/{$activity->id}", [
            'service_category_id' => $category->id, 'name' => 'Akt Y2', 'slug' => $activity->slug, 'is_published' => 1,
            'kbli_code' => '12345',
        ]);
        $log = $this->last();
        $this->assertSame(['updated', 'Aktivitas Usaha'], [$log->action, $log->menu]);
        $this->assertStringContainsString('"Akt Y2" — mengubah:', $log->detail);

        $this->post("/admin/gallery/{$activity->id}/images", ['images' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')]]);
        $log = $this->last();
        $this->assertSame(['created', 'Galeri'], [$log->action, $log->menu]);
        $this->assertStringContainsString('2 foto ditambahkan ke "Akt Y2"', $log->detail);

        $image = ActivityImage::first();
        $this->patch("/admin/gallery/{$activity->id}/images/{$image->id}", ['caption' => 'Deskripsi baru']);
        $this->assertStringContainsString('mengubah: Deskripsi', $this->last()->detail);

        $ids = ActivityImage::orderByDesc('sort_order')->pluck('id')->all();
        $this->put("/admin/gallery/{$activity->id}/order", ['order' => $ids]);
        $this->assertSame('reordered', $this->last()->action);

        $this->delete("/admin/gallery/{$activity->id}/images/{$image->id}");
        $this->assertSame(['deleted', 'Galeri'], [$this->last()->action, $this->last()->menu]);

        $second = BusinessActivity::factory()->for($category, 'category')->create();
        $this->putJson('/admin/activities/reorder', ['category' => $category->id, 'order' => [$second->id, $activity->id]])->assertOk();
        $log = $this->last();
        $this->assertSame(['reordered', 'Aktivitas Usaha'], [$log->action, $log->menu]);
        $this->assertStringContainsString('"Kat X"', $log->detail);

        $this->delete("/admin/activities/{$activity->id}");
        $log = $this->last();
        $this->assertSame(['deleted', 'Aktivitas Usaha'], [$log->action, $log->menu]);
        $this->assertStringContainsString('dari kategori "Kat X"', $log->detail);
    }

    public function test_page_content_changes_name_the_sections_and_reset_is_logged(): void
    {
        $texts = [];
        foreach (PageContents::fields('about') as $key => $field) {
            if (($field['type'] ?? 'text') !== 'image') {
                $texts[$key] = $field['default'];
            }
        }

        // Tidak ada yang berubah: tidak dicatat.
        $this->put('/admin/pages/about', ['content' => $texts]);
        $this->assertSame(0, ActivityLog::where('menu', 'Konten Halaman')->count());

        $this->put('/admin/pages/about', ['content' => array_merge($texts, ['cta_button' => 'Lihat Semua', 'hero_label' => 'Profil'])]);
        $log = $this->last();
        $this->assertSame(['updated', 'Konten Halaman'], [$log->action, $log->menu]);
        $this->assertSame('Tentang Kami — mengubah bagian: Bagian utama, CTA', $log->detail);

        $this->delete('/admin/pages/about');
        $log = $this->last();
        $this->assertSame(['reset', 'Konten Halaman'], [$log->action, $log->menu]);
        $this->assertStringContainsString('Tentang Kami — dikembalikan ke setelan default', $log->detail);
    }

    public function test_profile_password_login_and_logout_are_logged(): void
    {
        $this->patch('/admin/profil', ['name' => 'Nama Baru', 'email' => $this->admin->email]);
        $this->assertStringContainsString('mengubah: Nama', $this->last()->detail);
        $this->assertSame('Profil', $this->last()->menu);

        $this->put('/password', ['current_password' => 'password', 'password' => 'password-baru-123', 'password_confirmation' => 'password-baru-123']);
        $log = $this->last();
        $this->assertSame(['password', 'Profil', 'Password akun diubah'], [$log->action, $log->menu, $log->detail]);
        $this->assertSame('Ganti password', $log->actionLabel());

        auth()->logout();
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'password-baru-123']);
        $log = $this->last();
        $this->assertSame(['login', 'Akun'], [$log->action, $log->menu]);

        $this->post('/logout');
        $log = $this->last();
        $this->assertSame(['logout', 'Akun'], [$log->action, $log->menu]);
        $this->assertSame('Nama Baru', $log->user_name);
    }

    public function test_log_page_shows_entries_newest_first_with_date_time_and_filters(): void
    {
        Carbon::setTestNow('2026-10-21 14:35:08');
        ActivityLogger::record('created', 'Kategori', 'Entri pertama');
        Carbon::setTestNow('2026-10-21 15:00:00');
        ActivityLogger::record('deleted', 'Galeri', 'Entri kedua');
        Carbon::setTestNow();

        $this->get('/admin/activity-log')
            ->assertOk()
            ->assertSeeInOrder(['Entri kedua', 'Entri pertama'])
            ->assertSee('21 Okt 2026')
            ->assertSee('14:35:08')
            ->assertSee('Tambah')
            ->assertSee('Hapus')
            ->assertSee('Admin Uji')
            ->assertSee('2 catatan');

        $this->get('/admin/activity-log?menu=Galeri')->assertSee('Entri kedua')->assertDontSee('Entri pertama')->assertSee('1 catatan');
        $this->get('/admin/activity-log?action=created')->assertSee('Entri pertama')->assertDontSee('Entri kedua');
        $this->get('/admin/activity-log?q=kedua')->assertSee('Entri kedua')->assertDontSee('Entri pertama');
        $this->get('/admin/activity-log?q=tidak-ada')->assertSee('Tidak ada catatan yang cocok');
        // Nilai filter yang tidak dikenal diabaikan.
        $this->get('/admin/activity-log?menu=Sembarang&action=xyz')->assertOk()->assertSee('2 catatan');
    }

    public function test_pagination_and_empty_state(): void
    {
        $this->get('/admin/activity-log')->assertSee('Belum ada aktivitas yang tercatat');

        for ($i = 1; $i <= 30; $i++) {
            ActivityLogger::record('updated', 'Profil', "Catatan nomor {$i}");
        }

        $this->get('/admin/activity-log')->assertSee('Catatan nomor 30')->assertDontSee('Catatan nomor 1<')->assertSee('30 catatan');
        $this->get('/admin/activity-log?page=2')->assertSee('Catatan nomor 5')->assertDontSee('Catatan nomor 30');
    }

    public function test_history_survives_a_deleted_account_and_long_details_are_trimmed(): void
    {
        ActivityLogger::record('updated', 'Profil', str_repeat('a', 900));
        $this->assertSame(500, mb_strlen(ActivityLog::first()->detail));

        $other = User::factory()->create(['name' => 'Admin Lama']);
        ActivityLogger::record('created', 'Kategori', 'Dibuat oleh admin lama', $other);
        $other->delete();

        $log = ActivityLog::where('detail', 'Dibuat oleh admin lama')->first();
        $this->assertNull($log->user_id);
        $this->assertSame('Admin Lama', $log->user_name);
        $this->get('/admin/activity-log')->assertSee('Admin Lama');
    }

    public function test_a_failing_log_never_breaks_the_real_action(): void
    {
        \Illuminate\Support\Facades\Schema::drop('activity_logs');

        $this->post('/admin/categories', ['name' => 'Tetap Tersimpan', 'description' => 'x', 'is_published' => 1])->assertRedirect();

        $this->assertDatabaseHas('service_categories', ['name' => 'Tetap Tersimpan']);
    }

    public function test_actions_show_a_small_icon_and_details_are_centered(): void
    {
        foreach (array_keys(ActivityLog::actions()) as $action) {
            ActivityLogger::record($action, 'Profil', "Uji {$action}");
        }

        $html = $this->get('/admin/activity-log')->assertOk()->getContent();

        foreach (ActivityLog::actions() as [$label, $class, $icon]) {
            $this->assertStringContainsString('d="'.$icon.'"', $html, "ikon {$label}");
            $this->assertStringContainsString($label, $html);
        }

        // Keterangan rata tengah, dan ganti password punya aksinya sendiri (juga di filter).
        $this->assertStringContainsString('class="min-w-64 px-4 py-3 text-center text-gray-700"', $html);
        $this->get('/admin/activity-log?action=password')->assertSee('Uji password')->assertDontSee('Uji login');
        $this->assertStringContainsString('<option value="password"', $html);
    }

    private function logAt(string $datetime, string $detail): void
    {
        $log = ActivityLog::create(['user_id' => $this->admin->id, 'user_name' => 'Admin Uji', 'action' => 'updated', 'menu' => 'Profil', 'detail' => $detail]);
        $log->forceFill(['created_at' => $datetime])->save();
    }

    public function test_date_range_filter_is_inclusive_swaps_reversed_dates_and_ignores_invalid_ones(): void
    {
        $this->logAt('2026-10-01 00:00:00', 'Awal Oktober');
        $this->logAt('2026-10-10 23:59:59', 'Akhir sepuluh');
        $this->logAt('2026-10-11 00:00:01', 'Awal sebelas');
        $this->logAt('2026-10-20 12:00:00', 'Tengah dua puluh');

        // Satu hari tertentu (dari = sampai), termasuk jam terakhir hari itu.
        $this->get('/admin/activity-log?dari=2026-10-10&sampai=2026-10-10')
            ->assertSee('Akhir sepuluh')->assertDontSee('Awal sebelas')->assertDontSee('Awal Oktober')->assertSee('1 catatan');

        // Rentang: kedua ujung ikut.
        $this->get('/admin/activity-log?dari=2026-10-01&sampai=2026-10-11')
            ->assertSee('Awal Oktober')->assertSee('Akhir sepuluh')->assertSee('Awal sebelas')->assertDontSee('Tengah dua puluh')->assertSee('3 catatan');

        // Hanya batas awal atau hanya batas akhir.
        $this->get('/admin/activity-log?dari=2026-10-11')->assertSee('Awal sebelas')->assertSee('Tengah dua puluh')->assertDontSee('Akhir sepuluh');
        $this->get('/admin/activity-log?sampai=2026-10-01')->assertSee('Awal Oktober')->assertDontSee('Akhir sepuluh');

        // Terbalik: otomatis ditukar.
        $this->get('/admin/activity-log?dari=2026-10-11&sampai=2026-10-01')->assertSee('3 catatan')->assertSee('value="2026-10-01"', false);

        // Tidak valid atau mustahil: diabaikan (semua tampil).
        foreach (['dari=abc', 'dari=2026-02-31', 'sampai=2026-13-01', 'dari[]=x'] as $query) {
            $this->get('/admin/activity-log?'.$query)->assertOk()->assertSee('4 catatan');
        }
    }

    public function test_date_filter_combines_with_other_filters_and_shows_presets(): void
    {
        $today = now()->timezone(config('app.timezone'));
        ActivityLogger::record('created', 'Kategori', 'Catatan hari ini');
        $this->logAt($today->copy()->subDays(3)->format('Y-m-d').' 10:00:00', 'Catatan tiga hari lalu');
        $this->logAt($today->copy()->subDays(20)->format('Y-m-d').' 10:00:00', 'Catatan dua puluh hari lalu');

        $response = $this->get('/admin/activity-log')
            ->assertSee('Rentang tanggal')
            ->assertSee('name="dari"', false)
            ->assertSee('name="sampai"', false)
            ->assertSee('Hari ini')->assertSee('7 hari terakhir')->assertSee('30 hari terakhir');

        // Pintasan membawa rentang yang benar dan menandai dirinya aktif.
        $this->get('/admin/activity-log?dari='.$today->copy()->subDays(6)->toDateString().'&sampai='.$today->toDateString())
            ->assertSee('Catatan hari ini')->assertSee('Catatan tiga hari lalu')->assertDontSee('Catatan dua puluh hari lalu')
            ->assertSee('border-primary bg-primary text-white', false);

        $this->get('/admin/activity-log?dari='.$today->toDateString().'&sampai='.$today->toDateString())
            ->assertSee('Catatan hari ini')->assertDontSee('Catatan tiga hari lalu');

        // Digabung dengan menu dan pencarian.
        $this->get('/admin/activity-log?menu=Kategori&dari='.$today->copy()->subDays(30)->toDateString())->assertSee('Catatan hari ini')->assertDontSee('Catatan tiga hari lalu');
        $this->get('/admin/activity-log?q=tiga&dari='.$today->copy()->subDays(30)->toDateString())->assertSee('Catatan tiga hari lalu')->assertDontSee('Catatan hari ini');

        // Rentang tanpa hasil, dan tombol Reset muncul.
        $this->get('/admin/activity-log?dari=2000-01-01&sampai=2000-01-02')->assertSee('Tidak ada catatan yang cocok')->assertSee('Reset');
    }

    public function test_date_filter_is_kept_across_pages(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            $this->logAt('2026-10-05 10:00:00', "Catatan rentang {$i}");
        }
        $this->logAt('2026-09-01 10:00:00', 'Di luar rentang');

        $this->get('/admin/activity-log?dari=2026-10-01&sampai=2026-10-31')
            ->assertSee('30 catatan')
            ->assertSee('dari=2026-10-01&amp;sampai=2026-10-31&amp;page=2', false)
            ->assertDontSee('Di luar rentang');
    }
}
