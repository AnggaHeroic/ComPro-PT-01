<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Batas tidak aktif admin
    |--------------------------------------------------------------------------
    |
    | Admin otomatis keluar dari dashboard bila tidak ada aktivitas selama
    | "idle_timeout" menit. "idle_warning" menit sebelumnya, dashboard
    | menampilkan peringatan dengan hitung mundur dan tombol "Tetap masuk".
    |
    */

    'idle_timeout' => (int) env('ADMIN_IDLE_TIMEOUT', 30),

    'idle_warning' => (int) env('ADMIN_IDLE_WARNING', 2),

    /*
    |--------------------------------------------------------------------------
    | Akun admin awal
    |--------------------------------------------------------------------------
    |
    | Dibuat otomatis oleh "php artisan migrate" saat database masih tanpa akun
    | (lihat migrasi seed_initial_site_data). Isi di .env sebelum deploy.
    |
    */

    'account' => [
        'name' => env('ADMIN_NAME', 'Admin'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

];
