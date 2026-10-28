<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Catatan "keluar otomatis" ditulis ulang agar lamanya ada di dalam kurung (tampil di baris kedua),
     * dan perubahan profil mendapat aksinya sendiri ("profile") terpisah dari "updated" biasa.
     */
    public function up(): void
    {
        DB::table('activity_logs')->where('action', 'timeout')->orderBy('id')->each(function ($row) {
            $new = preg_replace('/^Keluar otomatis karena tidak aktif selama (\d+) menit$/u', 'Keluar otomatis (tidak aktif selama $1 menit)', (string) $row->detail, 1, $count);

            if ($count > 0) {
                DB::table('activity_logs')->where('id', $row->id)->update(['detail' => $new]);
            }
        });

        DB::table('activity_logs')
            ->where('action', 'updated')
            ->where('menu', 'Profil')
            ->where('detail', 'like', 'Mengubah informasi profil%')
            ->update(['action' => 'profile']);
    }

    public function down(): void
    {
        DB::table('activity_logs')->where('action', 'profile')->update(['action' => 'updated']);
    }
};
