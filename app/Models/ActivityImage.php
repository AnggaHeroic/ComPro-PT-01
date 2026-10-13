<?php

namespace App\Models;

use App\Models\Concerns\ResolvesImageUrl;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['business_activity_id', 'image_path', 'caption', 'sort_order'])]
class ActivityImage extends Model
{
    /** @use HasFactory<\Database\Factories\ActivityImageFactory> */
    use HasFactory, ResolvesImageUrl;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(BusinessActivity::class, 'business_activity_id');
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
