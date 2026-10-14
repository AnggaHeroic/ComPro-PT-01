<?php

namespace App\Models;

use App\Models\Concerns\ResolvesImageUrl;
use App\Support\Slug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'service_category_id', 'kbli_code', 'name', 'slug', 'short_description',
    'description', 'image_path', 'sort_order', 'is_published',
])]
class BusinessActivity extends Model
{
    /** @use HasFactory<\Database\Factories\BusinessActivityFactory> */
    use HasFactory, ResolvesImageUrl;

    /**
     * Slug dibuat otomatis dari nama saat aktivitas diterbitkan (unik di dalam
     * kategorinya). Draft tidak memiliki slug; slug yang sudah ada dipertahankan,
     * kecuali bentrok setelah aktivitas dipindah ke kategori lain.
     */
    protected static function booted(): void
    {
        static::saving(function (self $activity) {
            if (! $activity->is_published) {
                return;
            }

            $taken = fn (string $slug) => static::query()
                ->where('service_category_id', $activity->service_category_id)
                ->where('slug', $slug)
                ->when($activity->exists, fn ($query) => $query->whereKeyNot($activity->getKey()))
                ->exists();

            if (blank($activity->slug) || ($activity->isDirty('service_category_id') && $taken($activity->slug))) {
                $activity->slug = Slug::unique((string) $activity->name, $taken, 150);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ActivityImage::class)->ordered();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->resolveImageUrl($this->image_path));
    }
}
