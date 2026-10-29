<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // "Revert default" dan "Reset konten" kini satu aksi: "Reset default".
        DB::table('activity_logs')->where('action', 'default_revert')->update(['action' => 'reset']);
    }

    public function down(): void
    {
        // Penggabungan tidak dapat dibedakan lagi; tidak ada yang dikembalikan.
    }
};
