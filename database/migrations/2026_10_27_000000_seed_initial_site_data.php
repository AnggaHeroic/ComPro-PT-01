<?php

use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Data awal situs ikut terpasang oleh "php artisan migrate" (tanpa --seed): akun admin dari .env
     * dan katalog bawaan (kategori + aktivitas usaha berdasarkan data KBLI perusahaan).
     *
     * Hanya mengisi yang masih kosong, jadi database yang sudah berisi tidak tertimpa. Tes memakai
     * database kosong sendiri dan tidak menjalankan ini.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        if (! User::query()->exists()) {
            if (! config('admin.account.email') || ! config('admin.account.password')) {
                throw new RuntimeException('Isi ADMIN_EMAIL dan ADMIN_PASSWORD di .env, lalu jalankan "php artisan migrate" lagi (akun admin awal dibuat dari keduanya).');
            }

            (new AdminUserSeeder)->run();
        }

        if (! ServiceCategory::query()->exists()) {
            (new ServiceCatalogSeeder)->run();
        }
    }

    public function down(): void
    {
        // Data yang sudah dibuat tidak dihapus saat rollback.
    }
};
