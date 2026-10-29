# Deploy dengan MySQL

Selama pengembangan situs memakai SQLite (`database/database.sqlite`). Kode, migrasi, dan seluruh tes
sudah diuji di MySQL 8. Di server cukup menjalankan `php artisan migrate`: tabel dibuat, lalu akun admin
dan katalog bawaan (5 kategori + 14 aktivitas usaha berdasarkan data KBLI perusahaan) terpasang otomatis.

## 1. Siapkan database MySQL
Di phpMyAdmin (atau hosting) buat database kosong, mis. `compro_pt`, collation `utf8mb4_unicode_ci`.

## 2. Isi `.env` di server
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=compro_pt1
DB_USERNAME=...
DB_PASSWORD=...

ADMIN_NAME=Admin
ADMIN_EMAIL=...        # email untuk login admin
ADMIN_PASSWORD=...     # password awal; ganti lewat menu Profil setelah login
```
Lalu `php artisan config:clear` (jika memakai `php artisan serve`, matikan dan jalankan ulang).

## 3. Migrasi
```
php artisan migrate --force
```
Selain membuat tabel, migrasi `seed_initial_site_data` mengisi:
- akun admin dari `ADMIN_*` (jika `ADMIN_EMAIL` / `ADMIN_PASSWORD` kosong, migrasi berhenti dengan pesan
  yang jelas; isi lalu jalankan lagi),
- kategori dan aktivitas usaha bawaan.

Hanya mengisi yang masih kosong: database yang sudah punya akun atau kategori tidak ditimpa, dan
menjalankannya ulang tidak membuat data ganda. Konten halaman (Beranda, Tentang Kami, dst.) tidak perlu
diisi; teks bawaannya tampil sampai admin mengubahnya.

## 4. Berkas unggahan
Foto yang diunggah admin tersimpan sebagai file (bukan di database). Bila ada yang ingin dibawa dari
komputer pengembangan, salin folder `storage/app/public` dan jalankan `php artisan storage:link`.

## Kembali ke SQLite
Ubah `DB_CONNECTION=sqlite` di `.env` dan `php artisan config:clear`.
