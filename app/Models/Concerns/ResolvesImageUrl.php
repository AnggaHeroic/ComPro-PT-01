<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait ResolvesImageUrl
{
    /**
     * URL eksternal (placeholder) dipakai apa adanya; path relatif diambil dari disk public.
     */
    protected function resolveImageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
