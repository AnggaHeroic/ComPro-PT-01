<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pengolahan Gambar Upload
    |--------------------------------------------------------------------------
    |
    | Semua gambar yang diunggah admin dikonversi ke WebP. Kualitas 88 pada
    | WebP lossy secara visual hampir tidak bisa dibedakan dari sumbernya,
    | namun ukurannya jauh lebih kecil dari JPEG/PNG. Gambar yang lebih lebar
    | dari max_width diperkecil; gambar yang lebih kecil tidak pernah diperbesar.
    |
    */

    'disk' => env('IMAGES_DISK', 'public'),

    'quality' => (int) env('IMAGES_WEBP_QUALITY', 88),

    'max_width' => (int) env('IMAGES_MAX_WIDTH', 2000),

    // Batas ukuran file sumber (KB) dan dimensi piksel untuk validasi upload.
    'max_upload_kb' => 5120,

    'max_dimension' => 8000,

];
