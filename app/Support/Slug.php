<?php

namespace App\Support;

use Illuminate\Support\Str;

class Slug
{
    /**
     * Buat slug dari nama dan pastikan unik; bila sudah dipakai ditambah -2, -3, dst.
     *
     * @param  callable(string): bool  $exists  mengembalikan true bila slug sudah terpakai
     */
    public static function unique(string $name, callable $exists, int $maxLength = 120): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? Str::limit($base, $maxLength - 6, '') : 'item';
        $base = rtrim($base, '-');

        $slug = $base;
        $counter = 2;

        while ($exists($slug)) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
