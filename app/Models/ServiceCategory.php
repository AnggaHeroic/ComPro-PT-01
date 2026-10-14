<?php

namespace App\Models;

use App\Models\Concerns\ResolvesImageUrl;
use App\Support\CategoryIcons;
use App\Support\Slug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'image_path', 'icon', 'icon_image_path', 'sort_order', 'is_published'])]
class ServiceCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceCategoryFactory> */
    use HasFactory, ResolvesImageUrl;

    /**
     * Slug dibuat otomatis dari nama saat kategori diterbitkan. Draft tidak
     * memiliki slug; slug yang sudah ada dipertahankan agar URL tidak berubah.
     */
    protected static function booted(): void
    {
        static::saving(function (self $category) {
            if ($category->is_published && blank($category->slug)) {
                $category->slug = Slug::unique(
                    (string) $category->name,
                    fn (string $slug) => static::query()
                        ->where('slug', $slug)
                        ->when($category->exists, fn ($query) => $query->whereKeyNot($category->getKey()))
                        ->exists(),
                    120,
                );
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

    public function activities(): HasMany
    {
        return $this->hasMany(BusinessActivity::class)->ordered();
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

    protected function iconImageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->resolveImageUrl($this->icon_image_path));
    }

    /**
     * Kunci ikon whitelist yang dipakai bila tidak ada ikon kustom.
     */
    protected function resolvedIcon(): Attribute
    {
        return Attribute::get(
            fn () => CategoryIcons::isValid($this->icon) ? $this->icon : CategoryIcons::forSlug($this->slug)
        );
    }
}
