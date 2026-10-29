<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hanya menyimpan teks yang diubah admin; teks bawaan ada di App\Support\PageContents.
        Schema::create('page_contents', function (Blueprint $table) {
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
        Schema::dropIfExists('page_contents');
    }
};
