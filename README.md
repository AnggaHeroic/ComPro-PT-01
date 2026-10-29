# ComPro PT. Putra Maju Sukses

Situs profil perusahaan PT. Putra Maju Sukses beserta CMS admin untuk mengelola konten halaman, kategori usaha, aktivitas usaha, dan galeri. Dibangun dengan Laravel 13, Blade, Tailwind CSS v4, dan Alpine.js.

## Kebutuhan

| Perangkat | Versi |
|---|---|
| PHP | 8.3 atau lebih baru (ekstensi `pdo_mysql`, `mbstring`, `gd` dengan dukungan WebP) |
| Composer | 2.x |
| Node.js dan npm | Node.js 20.19 atau lebih baru |
| Database | MySQL 8 |

Pengguna Laragon sudah mendapat PHP, Composer, Node.js, dan MySQL sekaligus.

## Instalasi

### 1. Ambil kode dan pasang dependensi

```bash
git clone <alamat-repositori> ComPro-PT
cd ComPro-PT

composer install
npm install
```

### 2. Buat berkas `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Di Windows PowerShell, ganti `cp` dengan `copy`.

### 3. Siapkan database

Nyalakan MySQL (di Laragon: **Start All**), lalu buat database kosong, misalnya lewat phpMyAdmin, bernama `compro_pt1` dengan collation `utf8mb4_unicode_ci`. Setelah itu cocokkan isian ini di `.env` dengan nama database, pengguna, dan password MySQL kamu:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=compro_pt1
DB_USERNAME=root
DB_PASSWORD=
```

Nilai ini sudah menjadi bawaan `.env.example`. Ubah `DB_DATABASE` bila kamu memakai nama database lain.

### 4. Atur akun admin

Isi data login admin di `.env`. Akun dibuat otomatis saat migrasi:

```env
ADMIN_NAME=Admin
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=ganti-dengan-password-anda
```

`ADMIN_EMAIL` dan `ADMIN_PASSWORD` wajib terisi. Jika kosong, migrasi berhenti dengan pesan yang jelas.

### 5. Jalankan migrasi

```bash
php artisan migrate
```

Selain membuat tabel, perintah ini mengisi akun admin dan katalog bawaan (5 kategori dan 14 aktivitas usaha). Bagian yang sudah ada tidak ditimpa, jadi aman dijalankan ulang.

### 6. Tautkan folder unggahan

```bash
php artisan storage:link
```

Foto galeri dan gambar halaman yang diunggah admin tersimpan di `storage/app/public`. Perintah ini membuatnya dapat diakses dari browser.

### 7. Jalankan aplikasi

Buka dua terminal.

**Terminal 1: aset (CSS dan JavaScript).**

```bash
npm run dev
```

**Terminal 2: server Laravel.**

```bash
php artisan serve
```

Buka [http://localhost:8000](http://localhost:8000) untuk situs publik. Halaman login admin ada di [http://localhost:8000/login](http://localhost:8000/login), memakai `ADMIN_EMAIL` dan `ADMIN_PASSWORD` dari langkah 4. Setelah masuk, dashboard ada di `/admin`.

Jika hanya ingin melihat hasilnya tanpa mengembangkan, jalankan `npm run build` sekali sebagai ganti `npm run dev`, lalu cukup `php artisan serve`.

## Masalah yang sering muncul

- **Halaman tampil tanpa gaya (CSS tidak termuat).** `npm run dev` belum jalan. Jalankan, atau hapus berkas `public/hot` lalu jalankan `npm run build`.
- **Perubahan `.env` tidak terbaca.** Jalankan `php artisan config:clear`. Jika memakai `php artisan serve`, matikan servernya lalu jalankan ulang.
- **Galat `SQLSTATE[HY000] [2002]` atau `Access denied`.** MySQL belum menyala, atau `DB_*` di `.env` tidak cocok. Periksa nama database, pengguna, dan password, lalu `php artisan config:clear`.
- **Galat `could not find driver`.** Ekstensi PHP `pdo_mysql` belum aktif di `php.ini`.
- **Galat `No application encryption key has been specified`.** Jalankan `php artisan key:generate`.
- **Foto yang diunggah tidak tampil.** Jalankan `php artisan storage:link`.

## Lanjut

- Men-deploy ke server: lihat [docs/pindah-ke-mysql.md](docs/pindah-ke-mysql.md).
- Menjalankan tes: `php artisan test`.
