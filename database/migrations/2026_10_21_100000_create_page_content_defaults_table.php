<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Setelan default kustom per halaman: isi yang ditetapkan admin sebagai acuan "Kembali ke setelan default".
        // Hanya kolom yang berbeda dari teks bawaan sistem yang disimpan.
        Schema::create('page_content_defaults', function (Blueprint $table) {
            $table->id();
            $table->string('page', 40);
            $table->string('key', 80);
            $table->text('value');
            $table->timestamps();

            $table->unique(['page', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_content_defaults');
    }
};
