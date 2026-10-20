<?php

namespace App\Support;

use App\Models\PageContent;

/**
 * Daftar teks halaman yang boleh diubah admin beserta teks bawaannya.
 *
 * Tata letak tetap ada di file Blade; admin hanya mengganti isi teks. Tiap
 * halaman publik memanggil objek ini sebagai $content('kunci') dan memakai
 * teks bawaan bila admin belum mengubahnya.
 */
class PageContents
{
    /** @var array<string, string>|null */
    private ?array $stored = null;

    public function __construct(private readonly string $page)
    {
    }

    public static function for(string $page): self
    {
        return new self($page);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function definitions(): array
    {
        return [
            'home' => [
                'label' => 'Beranda',
                'description' => 'Teks pada halaman utama website.',
                'sections' => [
                    'hero' => [
                        'label' => 'Bagian utama (Hero)',
                        'description' => 'Bagian paling atas halaman. Judul tampil sebagai satu kalimat; kata sorot berwarna merah.',
                        'fields' => [
                            'hero_eyebrow' => ['label' => 'Teks kecil di atas judul', 'default' => 'PT. Putra Maju Sukses', 'max' => 80],
                            'hero_title_before' => ['label' => 'Judul: bagian awal', 'default' => 'Produk dan', 'max' => 80],
                            'hero_title_highlight' => ['label' => 'Judul: kata sorot (merah)', 'default' => 'Solusi', 'max' => 40],
                            'hero_title_after' => ['label' => 'Judul: bagian akhir', 'default' => 'untuk Berbagai Kebutuhan Bisnis.', 'max' => 80],
                            'hero_description' => ['label' => 'Deskripsi', 'default' => 'PT. Putra Maju Sukses bergerak dalam berbagai bidang perdagangan dan layanan, mencakup teknologi informasi, kebutuhan rumah tangga, olahraga, peralatan industri, konstruksi, dan berbagai kebutuhan lainnya.', 'type' => 'textarea', 'max' => 400],
                            'hero_button_primary' => ['label' => 'Tombol utama', 'default' => 'Tentang Kami', 'max' => 30, 'hint' => 'Tautan tombol tetap ke halaman Tentang Kami.'],
                            'hero_button_secondary' => ['label' => 'Tombol kedua', 'default' => 'Layanan & Jasa', 'max' => 30, 'hint' => 'Tautan tombol tetap ke halaman Layanan & Jasa.'],
                        ],
                    ],
                    'intro' => [
                        'label' => 'Tentang perusahaan',
                        'fields' => [
                            'intro_label' => ['label' => 'Label kecil', 'default' => 'Tentang Perusahaan', 'max' => 50],
                            'intro_title_line1' => ['label' => 'Judul: baris pertama', 'default' => 'Beragam bidang.', 'max' => 60],
                            'intro_title_line2' => ['label' => 'Judul: baris kedua (merah)', 'default' => 'Satu perusahaan.', 'max' => 60],
                            'intro_description' => ['label' => 'Deskripsi', 'default' => 'PT. Putra Maju Sukses merupakan perusahaan yang bergerak dalam berbagai bidang perdagangan dan layanan, mulai dari kebutuhan rumah tangga, teknologi informasi, peralatan industri, olahraga, hingga berbagai kebutuhan lainnya.', 'type' => 'textarea', 'max' => 500],
                            'intro_link' => ['label' => 'Tautan', 'default' => 'Mengenal perusahaan', 'max' => 40, 'hint' => 'Mengarah ke halaman Tentang Kami.'],
                            'intro_stat_number' => ['label' => 'Angka statistik', 'default' => '14', 'max' => 10],
                            'intro_stat_label' => ['label' => 'Judul statistik', 'default' => 'Bidang usaha', 'max' => 40],
                            'intro_stat_description' => ['label' => 'Keterangan statistik', 'default' => 'dalam berbagai sektor perdagangan dan layanan.', 'max' => 100],
                        ],
                    ],
                    'services' => [
                        'label' => 'Bidang usaha',
                        'description' => 'Kartu kategori di bawah judul ini diatur lewat menu Kategori.',
                        'fields' => [
                            'services_label' => ['label' => 'Label kecil', 'default' => 'Bidang Usaha', 'max' => 50],
                            'services_title_line1' => ['label' => 'Judul: baris pertama', 'default' => 'Beragam produk.', 'max' => 60],
                            'services_title_line2' => ['label' => 'Judul: baris kedua (merah)', 'default' => 'Beragam kebutuhan.', 'max' => 60],
                            'services_description' => ['label' => 'Deskripsi', 'default' => 'PT. Putra Maju Sukses menyediakan berbagai produk dan layanan untuk mendukung kebutuhan perdagangan, teknologi, industri, dan perlengkapan lainnya.', 'type' => 'textarea', 'max' => 400],
                            'services_link_services' => ['label' => 'Tautan pertama', 'default' => 'Lihat seluruh layanan', 'max' => 40, 'hint' => 'Mengarah ke halaman Layanan & Jasa.'],
                            'services_link_contact' => ['label' => 'Tautan kedua', 'default' => 'Hubungi kami', 'max' => 40, 'hint' => 'Mengarah ke halaman Kontak Kami.'],
                        ],
                    ],
                    'cta' => [
                        'label' => 'Ajakan menghubungi',
                        'description' => 'Telepon, email, dan alamat pada bagian ini belum bisa diubah dari sini.',
                        'fields' => [
                            'cta_label' => ['label' => 'Label kecil', 'default' => 'Terhubung Dengan Kami', 'max' => 50],
                            'cta_title_line1' => ['label' => 'Judul: bagian awal', 'default' => 'Punya kebutuhan bisnis?', 'max' => 80],
                            'cta_title_line2' => ['label' => 'Judul: bagian merah', 'default' => 'Mari berdiskusi.', 'max' => 60],
                            'cta_description' => ['label' => 'Deskripsi', 'default' => 'Hubungi PT. Putra Maju Sukses untuk mendapatkan informasi mengenai produk, layanan, dan kebutuhan bisnis Anda.', 'type' => 'textarea', 'max' => 300],
                            'cta_button' => ['label' => 'Tombol', 'default' => 'Hubungi Kami', 'max' => 30, 'hint' => 'Mengarah ke halaman Kontak Kami.'],
                        ],
                    ],
                    'seo' => [
                        'label' => 'Mesin pencari',
                        'fields' => [
                            'seo_description' => ['label' => 'Deskripsi singkat untuk Google', 'default' => 'PT. Putra Maju Sukses merupakan perusahaan yang bergerak dalam berbagai bidang perdagangan dan layanan.', 'type' => 'textarea', 'max' => 200, 'hint' => 'Tidak tampil di halaman; dipakai mesin pencari.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function has(string $page): bool
    {
        return array_key_exists($page, self::definitions());
    }

    /**
     * Semua kolom satu halaman dalam bentuk datar: kunci => definisi.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function fields(string $page): array
    {
        $fields = [];
        foreach (self::definitions()[$page]['sections'] ?? [] as $section) {
            $fields += $section['fields'];
        }

        return $fields;
    }

    /** Nilai yang dipakai: hasil suntingan admin, atau teks bawaan. */
    public function get(string $key): string
    {
        $this->stored ??= PageContent::where('page', $this->page)->pluck('value', 'key')->all();

        $value = $this->stored[$key] ?? null;

        return filled($value) ? $value : (string) (self::fields($this->page)[$key]['default'] ?? '');
    }

    public function __invoke(string $key): string
    {
        return $this->get($key);
    }
}
