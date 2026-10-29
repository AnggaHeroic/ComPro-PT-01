<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Services\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;

trait StoresUploadedImage
{
    /**
     * Simpan gambar dari field upload (default "image") sebagai WebP; kembalikan path relatif atau null.
     * Kegagalan pengolahan diubah menjadi error validasi, bukan 500.
     */
    protected function storeUploadedImage(Request $request, ImageUploader $images, string $directory, string $field = 'image', ?int $maxWidth = null): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        try {
            return $images->store($request->file($field), $directory, $maxWidth);
        } catch (InvalidArgumentException|RuntimeException $e) {
            report($e);

            throw ValidationException::withMessages([
                $field => 'Gambar tidak dapat diproses. Gunakan file JPG, PNG, atau WebP yang valid.',
            ]);
        }
    }
}
