<?php

namespace App\Support;

/**
 * Daftar ikon kategori yang boleh dipilih admin (whitelist).
 * Setiap kunci harus punya komponen resources/views/components/icons/{kunci}.blade.php.
 * Admin tidak pernah memasukkan markup SVG; hanya memilih kunci dari daftar ini.
 */
class CategoryIcons
{
    /**
     * Ikon default per slug kategori awal; kategori lain memakai DEFAULT.
     *
     * @var array<string, string>
     */
    private const BY_SLUG = [
        'technology-digital' => 'computer',
        'home-lifestyle' => 'house',
        'sports-music' => 'sports',
        'industrial-machinery' => 'gearcog',
        'construction-supplies' => 'warehouse',
    ];

    public const DEFAULT = 'warehouse';

    /**
     * @return array<string, string> kunci => label
     */
    public static function options(): array
    {
        return [
            'computer' => 'Komputer',
            'chip' => 'Elektronik',
            'globe' => 'Internet / Global',
            'house' => 'Rumah',
            'utensils' => 'Peralatan dapur',
            'blocks' => 'Mainan',
            'sports' => 'Olahraga',
            'music' => 'Musik',
            'gearcog' => 'Mesin',
            'snowflake' => 'Pendingin',
            'bolt' => 'Listrik',
            'warehouse' => 'Gudang',
            'hammer' => 'Konstruksi',
            'building' => 'Gedung',
            'pencil' => 'Alat tulis',
            'package' => 'Barang / Paket',
            'truck' => 'Pengiriman',
            'cart' => 'Perdagangan',
            'briefcase' => 'Bisnis',
            'lightbulb' => 'Inovasi',
            'shield' => 'Keamanan',
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::options());
    }

    public static function forSlug(?string $slug): string
    {
        return self::BY_SLUG[$slug] ?? self::DEFAULT;
    }

    public static function isValid(?string $key): bool
    {
        return $key !== null && array_key_exists($key, self::options());
    }
}
