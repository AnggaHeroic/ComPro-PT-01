<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SQLite membangun ulang tabel saat kolom diubah; foreign key hanya bisa
     * dimatikan sementara di luar transaksi, jadi migrasi ini tanpa transaksi.
     */
    public $withinTransaction = false;

    /**
     * Slug kosong (NULL) untuk draft: URL baru dibuat saat item diterbitkan.
     */
    public function up(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->string('slug', 120)->nullable()->change();
        });

        Schema::table('business_activities', function (Blueprint $table) {
            $table->string('slug', 150)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Slug NULL tidak boleh tersisa saat kolom kembali wajib diisi.
        // Digabung lewat PHP (bukan "||" SQL) agar sama di SQLite dan MySQL.
        foreach (['service_categories', 'business_activities'] as $table) {
            DB::table($table)->whereNull('slug')->pluck('id')->each(
                fn ($id) => DB::table($table)->where('id', $id)->update(['slug' => "draft-{$id}"])
            );
        }

        Schema::table('service_categories', function (Blueprint $table) {
            $table->string('slug', 120)->nullable(false)->change();
        });

        Schema::table('business_activities', function (Blueprint $table) {
            $table->string('slug', 150)->nullable(false)->change();
        });
    }
};
