<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')
                ->constrained('service_categories')
                ->restrictOnDelete();
            $table->string('kbli_code', 10)->nullable();
            $table->string('name');
            $table->string('slug', 150);
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->unique(['service_category_id', 'slug']);
            $table->index(
                ['service_category_id', 'is_published', 'sort_order'],
                'activities_category_published_order_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_activities');
    }
};
