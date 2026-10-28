<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Catatan "tambah" ditulis ulang agar keterangan tambahan ada di dalam kurung (tampil di baris
     * kedua), sama seperti catatan "ubah". Hanya pola yang dikenali yang diubah.
     *
     * @return list<array{0: string, 1: string, 2: string}> [menu, pola, pengganti]
     */
    private function rules(): array
    {
        return [
            ['Kategori', '/^Menambahkan kategori "(.+)" dengan status (Terbit|Draft)$/u', 'Menambahkan kategori "$1" (status: $2)'],
            ['Aktivitas Usaha', '/^Menambahkan aktivitas "(.+)" pada kategori "(.+)" dengan status (Terbit|Draft)$/u', 'Menambahkan aktivitas "$1" (kategori: $2, status: $3)'],
            ['Galeri', '/^Menambahkan (\d+) foto ke galeri aktivitas "(.+)"$/u', 'Menambahkan foto ke galeri aktivitas "$2" ($1 foto)'],
        ];
    }

    public function up(): void
    {
        $rules = $this->rules();

        DB::table('activity_logs')->where('action', 'created')->orderBy('id')->each(function ($row) use ($rules) {
            foreach ($rules as [$menu, $pattern, $replacement]) {
                if ($row->menu !== $menu || $row->detail === null) {
                    continue;
                }

                $new = preg_replace($pattern, $replacement, $row->detail, 1, $count);

                if ($count > 0) {
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
