<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Keterangan riwayat lama memakai tanda "—" dan kalimat singkat; ditulis ulang menjadi kalimat
     * yang lebih jelas. Hanya pola yang dikenali yang diubah; selebihnya dibiarkan.
     *
     * @return list<array{0: string, 1: string, 2: string}> [menu, pola, pengganti]
     */
    private function rules(): array
    {
        return [
            ['Kategori', '/^"(.+)" ditambahkan \((.+)\)$/u', 'Menambahkan kategori "$1" dengan status $2'],
            ['Kategori', '/^"(.+)" — mengubah: (.+)$/u', 'Mengubah kategori "$1" (yang diubah: $2)'],
            ['Kategori', '/^"(.+)" dihapus$/u', 'Menghapus kategori "$1"'],
            ['Kategori', '/^Urutan kategori diubah$/u', 'Mengubah urutan kategori'],
            ['Aktivitas Usaha', '/^"(.+)" ditambahkan pada kategori "(.+)" \((.+)\)$/u', 'Menambahkan aktivitas "$1" pada kategori "$2" dengan status $3'],
            ['Aktivitas Usaha', '/^"(.+)" — mengubah: (.+)$/u', 'Mengubah aktivitas "$1" (yang diubah: $2)'],
            ['Aktivitas Usaha', '/^"(.+)" dihapus dari kategori "(.+)"$/u', 'Menghapus aktivitas "$1" dari kategori "$2"'],
            ['Aktivitas Usaha', '/^Urutan aktivitas pada kategori "(.+)" diubah$/u', 'Mengubah urutan aktivitas pada kategori "$1"'],
            ['Galeri', '/^(\d+) foto ditambahkan ke "(.+)"$/u', 'Menambahkan $1 foto ke galeri aktivitas "$2"'],
            ['Galeri', '/^Foto pada "(.+)" — mengubah: (.+)$/u', 'Mengubah foto di galeri aktivitas "$1" (yang diubah: $2)'],
            ['Galeri', '/^1 foto dihapus dari "(.+)"$/u', 'Menghapus 1 foto dari galeri aktivitas "$1"'],
            ['Galeri', '/^Urutan foto pada "(.+)" diubah$/u', 'Mengubah urutan foto di galeri aktivitas "$1"'],
            ['Konten Halaman', '/^(.+) — mengubah bagian: (.+)$/u', 'Mengubah konten halaman $1 (bagian yang diubah: $2)'],
            ['Konten Halaman', '/^(.+) — setelan default baru ditetapkan.*$/u', 'Menetapkan konten halaman $1 yang tersimpan sebagai setelan default baru'],
            ['Konten Halaman', '/^(.+) — setelan default dikembalikan ke default semula.*$/u', 'Menghapus setelan default yang ditetapkan admin untuk halaman $1 (kembali ke default bawaan sistem)'],
            ['Konten Halaman', '/^(.+) — seluruh konten dikembalikan ke setelan default \(default (.+)\)$/u', 'Mengembalikan seluruh konten halaman $1 ke setelan default $2'],
            ['Konten Halaman', '/^(.+) — dikembalikan ke setelan default$/u', 'Mengembalikan seluruh konten halaman $1 ke setelan default'],
            ['Profil', '/^Informasi profil — mengubah: (.+)$/u', 'Mengubah informasi profil (yang diubah: $1)'],
            ['Profil', '/^(?:Password akun diubah|Kata sandi diubah)$/u', 'Mengganti password akun'],
        ];
    }

    public function up(): void
    {
        $rules = $this->rules();

        DB::table('activity_logs')->orderBy('id')->each(function ($row) use ($rules) {
            foreach ($rules as [$menu, $pattern, $replacement]) {
                if ($row->menu !== $menu || $row->detail === null) {
                    continue;
                }

                $new = preg_replace($pattern, $replacement, $row->detail, 1, $count);

                if ($count > 0) {
                    // "default ditetapkan admin" lama kini berbunyi "yang ditetapkan admin".
                    $new = str_replace('setelan default ditetapkan admin', 'setelan default yang ditetapkan admin', $new);
                    DB::table('activity_logs')->where('id', $row->id)->update(['detail' => $new]);

                    return;
                }
            }
        });
    }

    public function down(): void
    {
        // Kalimat lama tidak dipulihkan.
    }
};
