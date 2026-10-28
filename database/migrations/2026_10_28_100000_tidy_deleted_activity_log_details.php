<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Catatan "hapus" ditulis ulang agar keterangan tambahan ada di dalam kurung (tampil di baris
     * kedua), sama seperti catatan "tambah" dan "ubah". Hanya pola yang dikenali yang diubah.
     *
     * @return list<array{0: string, 1: string, 2: string}> [menu, pola, pengganti]
     */
    private function rules(): array
    {
        return [
            ['Aktivitas Usaha', '/^Menghapus aktivitas "(.+)" dari kategori "(.+)"$/u', 'Menghapus aktivitas "$1" (kategori: $2)'],
            ['Galeri', '/^Menghapus 1 foto dari galeri aktivitas "(.+)"$/u', 'Menghapus foto dari galeri aktivitas "$1" (1 foto)'],
        ];
    }

    public function up(): void
    {
        $rules = $this->rules();

        DB::table('activity_logs')->where('action', 'deleted')->orderBy('id')->each(function ($row) use ($rules) {
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
