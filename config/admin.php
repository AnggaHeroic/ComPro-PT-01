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

];
