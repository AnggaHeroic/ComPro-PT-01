<?php

use App\Support\CategoryIcons;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            // Kunci ikon dari whitelist CategoryIcons; null = ikon default berdasarkan slug.
            $table->string('icon', 40)->nullable()->after('image_path');
            // Ikon kustom hasil unggahan admin (gambar raster); lebih diutamakan dari $icon.
            $table->string('icon_image_path')->nullable()->after('icon');
        });

        // Kategori yang sudah ada mempertahankan ikon yang selama ini tampil di beranda.
        foreach (['technology-digital', 'home-lifestyle', 'sports-music', 'industrial-machinery', 'construction-supplies'] as $slug) {
            DB::table('service_categories')
                ->where('slug', $slug)
                ->whereNull('icon')
                ->update(['icon' => CategoryIcons::forSlug($slug)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropColumn(['icon', 'icon_image_path']);
        });
    }
};
