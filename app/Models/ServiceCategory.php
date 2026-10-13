<?php

namespace App\Models;

use App\Models\Concerns\ResolvesImageUrl;
use App\Support\CategoryIcons;
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
