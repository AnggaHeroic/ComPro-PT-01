<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Data perusahaan (Tentang Kami) dulu 8 kolom terpisah; kini satu daftar judul + isi.
        $defaults = [
            1 => ['Established', 'XXXX'],
            2 => ['Headquarters', 'Denpasar, Bali, Indonesia'],
            3 => ['Business Activities', '14 bidang usaha'],
            4 => ['Business Scope', 'Perdagangan dan layanan'],
        ];

        $keys = [];
        foreach (array_keys($defaults) as $n) {
            $keys[] = "facts_{$n}_label";
            $keys[] = "facts_{$n}_value";
        }

        $stored = DB::table('page_contents')->where('page', 'about')->whereIn('key', $keys)->pluck('value', 'key');

        if ($stored->isNotEmpty() && ! DB::table('page_contents')->where(['page' => 'about', 'key' => 'facts_items'])->exists()) {
            $items = [];
            foreach ($defaults as $n => [$title, $text]) {
                $items[] = ['title' => $stored["facts_{$n}_label"] ?? $title, 'text' => $stored["facts_{$n}_value"] ?? $text];
            }

            DB::table('page_contents')->insert([
                'page' => 'about',
                'key' => 'facts_items',
                'value' => json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('page_contents')->where('page', 'about')->whereIn('key', $keys)->delete();
    }

    public function down(): void
    {
        // Pemisahan kembali ke 8 kolom tidak didukung; isi tetap berada di daftar.
    }
};
