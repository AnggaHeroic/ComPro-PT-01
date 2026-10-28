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
        $this->assertStringContainsString('Menambahkan kategori "Kategori Baru" (status: Terbit)', $log->detail);
        $this->assertStringContainsString('Terbit', $log->detail);

        $category = ServiceCategory::where('name', 'Kategori Baru')->first();
        $this->put("/admin/categories/{$category->id}", ['name' => 'Kategori Diubah', 'slug' => $category->slug, 'description' => 'x', 'is_published' => 1]);
        $log = $this->last();
        $this->assertSame('updated', $log->action);
        $this->assertStringContainsString('Mengubah kategori "Kategori Diubah" (yang diubah: Nama)', $log->detail);
        $this->assertStringNotContainsString('Deskripsi', $log->detail);

        // Tanpa perubahan apa pun tidak ada catatan baru.
        $count = ActivityLog::count();
        $this->put("/admin/categories/{$category->id}", ['name' => 'Kategori Diubah', 'slug' => $category->slug, 'description' => 'x', 'is_published' => 1]);
        $this->assertSame($count, ActivityLog::count());

        $this->delete("/admin/categories/{$category->id}");
        $log = $this->last();
        $this->assertSame(['deleted', 'Kategori'], [$log->action, $log->menu]);
        $this->assertStringContainsString('Menghapus kategori "Kategori Diubah"', $log->detail);
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
        $this->assertStringContainsString('Mengubah aktivitas "Akt Y2" (yang diubah:', $log->detail);

        $this->post("/admin/gallery/{$activity->id}/images", ['images' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')]]);
        $log = $this->last();
        $this->assertSame(['created', 'Galeri'], [$log->action, $log->menu]);
        $this->assertStringContainsString('Menambahkan foto ke galeri aktivitas "Akt Y2" (2 foto)', $log->detail);

        $image = ActivityImage::first();
        $this->patch("/admin/gallery/{$activity->id}/images/{$image->id}", ['caption' => 'Deskripsi baru']);
        $this->assertStringContainsString('(yang diubah: Deskripsi', $this->last()->detail);

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
        $this->assertStringContainsString('(kategori: Kat X)', $log->detail);
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
        $this->assertSame('Mengubah konten halaman Tentang Kami (bagian yang diubah: Bagian utama, CTA)', $log->detail);

        $this->delete('/admin/pages/about');
        $log = $this->last();
        $this->assertSame(['reset', 'Konten Halaman'], [$log->action, $log->menu]);
        $this->assertStringContainsString('Mengembalikan seluruh konten halaman Tentang Kami ke setelan default bawaan sistem', $log->detail);
    }

    public function test_profile_password_login_and_logout_are_logged(): void
    {
        $this->patch('/admin/profil', ['name' => 'Nama Baru', 'email' => $this->admin->email]);
        $this->assertStringContainsString('(yang diubah: Nama)', $this->last()->detail);
        $this->assertSame('Profil', $this->last()->menu);
        $this->assertSame('profile', $this->last()->action);
        $this->assertSame('Ubah profil', $this->last()->actionLabel());

        $this->put('/password', ['current_password' => 'password', 'password' => 'password-baru-123', 'password_confirmation' => 'password-baru-123']);
        $log = $this->last();
        $this->assertSame(['password', 'Profil', 'Mengganti password akun'], [$log->action, $log->menu, $log->detail]);
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
        $this->get('/admin/activity-log?jenis=konten')->assertSee('Belum ada perubahan konten yang tercatat');
        $this->get('/admin/activity-log?jenis=akses')->assertSee('Belum ada catatan akses akun');

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

        $html = $this->get('/admin/activity-log?jenis=semua')->assertOk()->getContent();

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
        $this->get('/admin/activity-log?dari=2026-10-11&sampai=2026-10-01')->assertSee('3 catatan')->assertSee('01 Okt 2026 – 11 Okt 2026');

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

    public function test_date_filter_is_a_single_icon_button_with_a_panel_and_shows_the_active_range(): void
    {
        ActivityLogger::record('updated', 'Profil', 'Uji tombol tanggal');

        $html = $this->get('/admin/activity-log')->assertOk()->getContent();

        // Satu tombol ikon (kalender) membuka panel; tidak ada kolom tanggal di bar filter.
        $this->assertSame(1, substr_count($html, 'title="Filter tanggal"'));
        $this->assertStringContainsString('M16 3v4M8 3v4M3 10h18', $html);
        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('1 tanggal', $html);
        $this->assertStringContainsString('Rentang tanggal', $html);
        $this->assertStringContainsString('Terapkan', $html);
        $this->assertStringContainsString('name="dari"', $html);
        $this->assertStringContainsString('name="sampai"', $html);
        $this->assertStringNotContainsString('type="hidden" name="dari" value=', $html);

        // Tanpa filter tanggal: hanya ikon. Dengan filter: label rentang tampil di tombol.
        $this->assertStringNotContainsString('aria-label="Filter tanggal:', $html);

        $one = $this->get('/admin/activity-log?dari=2026-10-10&sampai=2026-10-10')->getContent();
        $this->assertStringContainsString('aria-label="Filter tanggal: 10 Okt 2026"', $one);
        $range = $this->get('/admin/activity-log?dari=2026-10-01&sampai=2026-10-10')->getContent();
        $this->assertStringContainsString('aria-label="Filter tanggal: 01 Okt 2026 – 10 Okt 2026"', $range);
        $this->get('/admin/activity-log?dari=2026-10-05')->assertSee('Sejak 05 Okt 2026');
        $this->get('/admin/activity-log?sampai=2026-10-05')->assertSee('Sampai 05 Okt 2026');
    }

    public function test_set_default_and_every_reset_use_the_names_setel_default_and_reset_default(): void
    {
        $actions = ActivityLog::actions();

        $this->assertSame('Setel default', $actions['default'][0]);
        $this->assertSame('Reset default', $actions['reset'][0]);
        // "Reset konten" dan "Revert default" sudah digabung ke "Reset default".
        $this->assertArrayNotHasKey('default_revert', $actions);
        $this->assertNotContains('Reset konten', array_column($actions, 0));
        $this->assertNotContains('Revert default', array_column($actions, 0));
        $this->assertNotContains('Set default', array_column($actions, 0));
        $this->assertSame(1, count(array_filter(array_column($actions, 0), fn ($label) => $label === 'Reset default')));

        $html = $this->get('/admin/activity-log')->getContent();
        $this->assertSame(1, substr_count($html, '>Reset default</option>'));
        $this->assertSame(1, substr_count($html, '>Setel default</option>'));
    }

    public function test_pagination_uses_the_admin_design_in_indonesian(): void
    {
        for ($i = 1; $i <= 60; $i++) {
            $this->logAt('2026-10-05 10:00:00', "Catatan halaman {$i}");
        }

        $first = $this->get('/admin/activity-log')->assertOk();
        $first->assertSee('Menampilkan')->assertSee('1–25')->assertSee('60')->assertSee('catatan')
            ->assertDontSee('Showing')->assertDontSee('results')
            ->assertSee('aria-label="Navigasi halaman"', false)
            ->assertSee('aria-label="Halaman berikutnya"', false)
            ->assertSee('rel="next"', false)
            // Halaman pertama: tombol sebelumnya nonaktif dan halaman 1 aktif.
            ->assertSee('aria-disabled="true"', false)
            ->assertSee('aria-current="page"', false);
        $this->assertStringNotContainsString('rel="prev"', $first->getContent());

        $second = $this->get('/admin/activity-log?page=2')->assertOk();
        $second->assertSee('26–50')->assertSee('rel="prev"', false)->assertSee('rel="next"', false);
        $this->assertMatchesRegularExpression('/aria-current="page"[^>]*>\s*2\s*</', $second->getContent());

        // Halaman terakhir: tombol berikutnya nonaktif.
        $last = $this->get('/admin/activity-log?page=3')->assertOk();
        $last->assertSee('51–60')->assertSee('rel="prev"', false);
        $this->assertStringNotContainsString('rel="next"', $last->getContent());

        // Satu halaman saja: tidak ada navigasi.
        $this->get('/admin/activity-log?q=halaman 7')->assertDontSee('Navigasi halaman');
    }

    public function test_old_style_details_are_rewritten_by_the_migration_and_unknown_ones_are_left_alone(): void
    {
        $old = [
            ['Kategori', '"A" ditambahkan (Draft)', 'Menambahkan kategori "A" dengan status Draft'],
            ['Kategori', '"A" — mengubah: Nama, Status', 'Mengubah kategori "A" (yang diubah: Nama, Status)'],
            ['Kategori', '"A" dihapus', 'Menghapus kategori "A"'],
            ['Kategori', 'Urutan kategori diubah', 'Mengubah urutan kategori'],
            ['Aktivitas Usaha', '"B" ditambahkan pada kategori "A" (Terbit)', 'Menambahkan aktivitas "B" pada kategori "A" dengan status Terbit'],
            ['Aktivitas Usaha', '"B" dihapus dari kategori "A"', 'Menghapus aktivitas "B" dari kategori "A"'],
            ['Galeri', '3 foto ditambahkan ke "B"', 'Menambahkan 3 foto ke galeri aktivitas "B"'],
            ['Galeri', '1 foto dihapus dari "B"', 'Menghapus 1 foto dari galeri aktivitas "B"'],
            ['Konten Halaman', 'Tentang Kami — mengubah bagian: Bagian utama, CTA', 'Mengubah konten halaman Tentang Kami (bagian yang diubah: Bagian utama, CTA)'],
            ['Konten Halaman', 'Beranda — seluruh konten dikembalikan ke setelan default (default yang ditetapkan admin)', 'Mengembalikan seluruh konten halaman Beranda ke setelan default yang ditetapkan admin'],
            ['Profil', 'Informasi profil — mengubah: Nama', 'Mengubah informasi profil (yang diubah: Nama)'],
            ['Profil', 'Kata sandi diubah', 'Mengganti password akun'],
            ['Kategori', 'Catatan bebas yang tidak dikenal', 'Catatan bebas yang tidak dikenal'],
        ];

        $ids = [];
        foreach ($old as [$menu, $detail]) {
            $ids[] = ActivityLog::create(['user_id' => null, 'user_name' => 'A', 'action' => 'updated', 'menu' => $menu, 'detail' => $detail])->id;
        }

        $migration = require database_path('migrations/2026_10_22_100000_reword_activity_log_details.php');
        $migration->up();
        $migration->up();

        foreach ($old as $index => [, , $expected]) {
            $this->assertSame($expected, ActivityLog::find($ids[$index])->detail);
        }
    }

    public function test_added_entries_are_tidied_by_the_migration_so_the_extra_info_sits_in_parentheses(): void
    {
        $rows = [
            ['Kategori', 'Menambahkan kategori "A" dengan status Draft', 'Menambahkan kategori "A" (status: Draft)'],
            ['Kategori', 'Menambahkan kategori "A (bekas)" dengan status Terbit', 'Menambahkan kategori "A (bekas)" (status: Terbit)'],
            ['Aktivitas Usaha', 'Menambahkan aktivitas "B" pada kategori "A" dengan status Terbit', 'Menambahkan aktivitas "B" (kategori: A, status: Terbit)'],
            ['Galeri', 'Menambahkan 3 foto ke galeri aktivitas "B"', 'Menambahkan foto ke galeri aktivitas "B" (3 foto)'],
            // Yang bukan "tambah" atau polanya tidak dikenal dibiarkan.
            ['Kategori', 'Menghapus kategori "A"', 'Menghapus kategori "A"'],
            ['Kategori', 'Catatan bebas', 'Catatan bebas'],
        ];

        $ids = [];
        foreach ($rows as [$menu, $detail]) {
            $action = str_starts_with($detail, 'Menambahkan') ? 'created' : 'deleted';
            $ids[] = ActivityLog::create(['user_name' => 'A', 'action' => $action, 'menu' => $menu, 'detail' => $detail])->id;
        }

        $migration = require database_path('migrations/2026_10_28_000000_tidy_added_activity_log_details.php');
        $migration->up();
        $migration->up();

        foreach ($rows as $index => [, , $expected]) {
            $this->assertSame($expected, ActivityLog::find($ids[$index])->detail);
        }

        $this->assertSame(['Menambahkan aktivitas "B"', '(kategori: A, status: Terbit)'], ActivityLog::find($ids[2])->detailLines());
    }

    public function test_deleted_entries_are_tidied_by_the_migration_the_same_way(): void
    {
        $rows = [
            ['Aktivitas Usaha', 'deleted', 'Menghapus aktivitas "B" dari kategori "A"', 'Menghapus aktivitas "B" (kategori: A)'],
            ['Aktivitas Usaha', 'deleted', 'Menghapus aktivitas "B (lama)" dari kategori "A (bekas)"', 'Menghapus aktivitas "B (lama)" (kategori: A (bekas))'],
            ['Galeri', 'deleted', 'Menghapus 1 foto dari galeri aktivitas "B"', 'Menghapus foto dari galeri aktivitas "B" (1 foto)'],
            // Kategori hanya satu baris; yang bukan "hapus" atau tak dikenal dibiarkan.
            ['Kategori', 'deleted', 'Menghapus kategori "A"', 'Menghapus kategori "A"'],
            ['Aktivitas Usaha', 'created', 'Menghapus aktivitas "B" dari kategori "A"', 'Menghapus aktivitas "B" dari kategori "A"'],
        ];

        $ids = [];
        foreach ($rows as [$menu, $action, $detail]) {
            $ids[] = ActivityLog::create(['user_name' => 'A', 'action' => $action, 'menu' => $menu, 'detail' => $detail])->id;
        }

        $migration = require database_path('migrations/2026_10_28_100000_tidy_deleted_activity_log_details.php');
        $migration->up();
        $migration->up();

        foreach ($rows as $index => [, , , $expected]) {
            $this->assertSame($expected, ActivityLog::find($ids[$index])->detail);
        }

        $this->assertSame(['Menghapus aktivitas "B"', '(kategori: A)'], ActivityLog::find($ids[0])->detailLines());
    }

    public function test_details_split_into_the_sentence_and_the_parenthesis_note_for_display(): void
    {
        $cases = [
            ['Mengubah kategori "Kat X" (yang diubah: Nama, Status)', ['Mengubah kategori "Kat X"', '(yang diubah: Nama, Status)']],
            ['Mengubah konten halaman Tentang Kami (bagian yang diubah: Data perusahaan)', ['Mengubah konten halaman Tentang Kami', '(bagian yang diubah: Data perusahaan)']],
            ['Menghapus setelan default yang ditetapkan admin untuk halaman Beranda (kembali ke default bawaan sistem)', ['Menghapus setelan default yang ditetapkan admin untuk halaman Beranda', '(kembali ke default bawaan sistem)']],
            // Tanda kurung di dalam nama (bukan di akhir kalimat) tidak dipecah.
            ['Menghapus kategori "Alat (bekas)"', ['Menghapus kategori "Alat (bekas)"', null]],
            ['Mengubah kategori "Alat (bekas)" (yang diubah: Nama)', ['Mengubah kategori "Alat (bekas)"', '(yang diubah: Nama)']],
            ['Menambahkan kategori "Baru" (status: Draft)', ['Menambahkan kategori "Baru"', '(status: Draft)']],
            ['Menambahkan aktivitas "Akt" (kategori: Kat, status: Terbit)', ['Menambahkan aktivitas "Akt"', '(kategori: Kat, status: Terbit)']],
            ['Menghapus aktivitas "Akt" (kategori: Kat)', ['Menghapus aktivitas "Akt"', '(kategori: Kat)']],
            ['Menghapus foto dari galeri aktivitas "Akt" (1 foto)', ['Menghapus foto dari galeri aktivitas "Akt"', '(1 foto)']],
            ['Masuk ke dashboard', ['Masuk ke dashboard', null]],
        ];

        foreach ($cases as [$detail, $expected]) {
            $this->assertSame($expected, (new ActivityLog(['detail' => $detail]))->detailLines(), $detail);
        }
        $this->assertSame(['', null], (new ActivityLog)->detailLines());
    }

    public function test_the_log_page_shows_the_note_on_its_own_second_line_and_keeps_the_stored_text(): void
    {
        $log = ActivityLog::create([
            'user_name' => 'Admin', 'action' => 'updated', 'menu' => 'Kategori',
            'detail' => 'Mengubah kategori "Kat X" (yang diubah: Nama)',
        ]);

        $html = $this->get('/admin/activity-log')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<p>Mengubah kategori &quot;Kat X&quot;</p>\s*<p class="mt-0\.5 text-xs text-gray-500">\(yang diubah: Nama\)</p>#', $html);
        $this->assertSame('Mengubah kategori "Kat X" (yang diubah: Nama)', $log->fresh()->detail);

        // Kalimat tanpa keterangan tambahan hanya satu baris.
        ActivityLog::create(['user_name' => 'Admin', 'action' => 'created', 'menu' => 'Kategori', 'detail' => 'Menghapus kategori "Baru"']);
        $html = $this->get('/admin/activity-log')->getContent();
        $this->assertMatchesRegularExpression('#<p>Menghapus kategori &quot;Baru&quot;</p>\s*</td>#', $html);
    }
}
