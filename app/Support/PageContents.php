<?php

namespace App\Support;

use App\Models\PageContent;
use Illuminate\Support\Facades\Storage;

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
            'about' => [
                'label' => 'Tentang Kami',
                'description' => 'Teks dan gambar pada halaman Tentang Kami.',
                'sections' => [
                    'hero' => [
                        'label' => 'Bagian utama',
                        'description' => 'Judul tampil dalam tiga baris; baris kedua berwarna merah.',
                        'fields' => [
                            'hero_label' => ['label' => 'Label kecil', 'default' => 'Tentang Kami', 'max' => 50],
                            'hero_title_line1' => ['label' => 'Judul: baris pertama', 'default' => 'Mengenal', 'max' => 40],
                            'hero_title_line2' => ['label' => 'Judul: baris kedua (merah)', 'default' => 'PT. Putra Maju Sukses', 'max' => 50],
                            'hero_title_line3' => ['label' => 'Judul: baris ketiga', 'default' => 'lebih dekat.', 'max' => 40],
                            'hero_description' => ['label' => 'Deskripsi', 'default' => 'PT. Putra Maju Sukses merupakan perusahaan yang bergerak dalam berbagai bidang perdagangan dan layanan untuk memenuhi kebutuhan pelanggan serta mitra bisnis.', 'type' => 'textarea', 'max' => 400],
                            'hero_image' => ['label' => 'Foto', 'type' => 'image', 'default' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1200&q=85', 'hint' => 'Tampil dengan rasio 4:3; sisi gambar dapat terpotong.'],
                            'hero_image_alt' => ['label' => 'Keterangan foto (untuk pembaca layar)', 'default' => 'Lingkungan kerja perusahaan', 'max' => 120],
                            'hero_caption_left' => ['label' => 'Teks bawah foto: kiri', 'default' => 'PT. Putra Maju Sukses', 'max' => 40],
                            'hero_caption_right' => ['label' => 'Teks bawah foto: kanan', 'default' => 'Denpasar, Bali', 'max' => 40],
                        ],
                    ],
                    'profile' => [
                        'label' => 'Mengenal perusahaan',
                        'fields' => [
                            'profile_label' => ['label' => 'Label kecil', 'default' => 'Mengenal Perusahaan', 'max' => 50],
                            'profile_title_line1' => ['label' => 'Judul: baris pertama', 'default' => 'Beragam bidang,', 'max' => 50],
                            'profile_title_line2' => ['label' => 'Judul: baris kedua (merah)', 'default' => 'satu perusahaan', 'max' => 50],
                            'profile_title_line3' => ['label' => 'Judul: baris ketiga', 'default' => 'yang terus berkembang.', 'max' => 50],
                            'profile_paragraph_1' => ['label' => 'Paragraf 1', 'default' => 'PT. Putra Maju Sukses merupakan perusahaan yang bergerak dalam berbagai bidang perdagangan dan layanan untuk memenuhi kebutuhan pelanggan serta mitra bisnis.', 'type' => 'textarea', 'max' => 800],
                            'profile_paragraph_2' => ['label' => 'Paragraf 2', 'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer tincidunt, justo vitae consequat efficitur, neque erat posuere lorem, sed tincidunt erat mauris vitae libero.', 'type' => 'textarea', 'max' => 800, 'optional' => true, 'hint' => 'Boleh dikosongkan; paragraf tidak akan tampil.'],
                            'profile_paragraph_3' => ['label' => 'Paragraf 3', 'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse potenti. Praesent tincidunt, mauris non consectetur interdum, erat neque consequat libero.', 'type' => 'textarea', 'max' => 800, 'optional' => true, 'hint' => 'Boleh dikosongkan; paragraf tidak akan tampil.'],
                            'profile_paragraph_4' => ['label' => 'Paragraf 4', 'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur tincidunt, mauris vitae consequat efficitur, neque libero posuere erat, sed tincidunt vestibulum sapien.', 'type' => 'textarea', 'max' => 800, 'optional' => true, 'hint' => 'Boleh dikosongkan; paragraf tidak akan tampil.'],
                            'profile_image' => ['label' => 'Foto', 'type' => 'image', 'default' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&q=85'],
                            'profile_image_alt' => ['label' => 'Keterangan foto (untuk pembaca layar)', 'default' => 'Lingkungan perusahaan', 'max' => 120],
                        ],
                    ],
                    'facts' => [
                        'label' => 'Data perusahaan',
                        'description' => 'Empat baris ringkasan di bawah foto.',
                        'fields' => [
                            'facts_1_label' => ['label' => 'Baris 1: judul', 'default' => 'Established', 'max' => 40],
                            'facts_1_value' => ['label' => 'Baris 1: isi', 'default' => 'XXXX', 'max' => 100],
                            'facts_2_label' => ['label' => 'Baris 2: judul', 'default' => 'Headquarters', 'max' => 40],
                            'facts_2_value' => ['label' => 'Baris 2: isi', 'default' => 'Denpasar, Bali, Indonesia', 'max' => 100],
                            'facts_3_label' => ['label' => 'Baris 3: judul', 'default' => 'Business Activities', 'max' => 40],
                            'facts_3_value' => ['label' => 'Baris 3: isi', 'default' => '14 bidang usaha', 'max' => 100],
                            'facts_4_label' => ['label' => 'Baris 4: judul', 'default' => 'Business Scope', 'max' => 40],
                            'facts_4_value' => ['label' => 'Baris 4: isi', 'default' => 'Perdagangan dan layanan', 'max' => 100],
                        ],
                    ],
                    'direction' => [
                        'label' => 'Visi & misi',
                        'fields' => [
                            'direction_label' => ['label' => 'Label bagian', 'default' => 'Visi & Misi', 'max' => 40],
                            'direction_tagline_before' => ['label' => 'Kalimat pengantar: bagian awal', 'default' => 'Arah yang menjadi dasar', 'max' => 80],
                            'direction_tagline_highlight' => ['label' => 'Kalimat pengantar: bagian merah', 'default' => 'perjalanan perusahaan.', 'max' => 80],
                            'vision_label' => ['label' => 'Visi: label', 'default' => 'Visi Perusahaan', 'max' => 40],
                            'vision_title' => ['label' => 'Visi: judul', 'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'type' => 'textarea', 'max' => 200],
                            'vision_text' => ['label' => 'Visi: uraian', 'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer tincidunt, justo vitae consequat efficitur, neque erat posuere lorem, sed tincidunt erat mauris vitae libero.', 'type' => 'textarea', 'max' => 500],
                            'mission_label' => ['label' => 'Misi: label', 'default' => 'Misi Utama', 'max' => 40],
                            'mission_items' => ['label' => 'Daftar misi', 'type' => 'list', 'item_label' => 'Misi', 'default' => [
                                'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer tincidunt, justo vitae consequat efficitur.',
                                'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse potenti. Praesent tincidunt mauris non consectetur interdum.',
                                'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur tincidunt, mauris vitae consequat efficitur.',
                            ], 'max' => 300, 'min_items' => 1, 'max_items' => 8, 'hint' => 'Tambah, hapus, atau ubah urutan misi. Tampilan kartu menyesuaikan jumlahnya.'],
                        ],
                    ],
                    'objectives' => [
                        'label' => 'Tujuan perusahaan',
                        'fields' => [
                            'objectives_label' => ['label' => 'Label kecil', 'default' => 'Tujuan Perusahaan', 'max' => 40],
                            'objectives_title_before' => ['label' => 'Judul: bagian awal', 'default' => 'Membangun usaha yang', 'max' => 60],
                            'objectives_title_highlight' => ['label' => 'Judul: bagian merah', 'default' => 'bernilai dan berkelanjutan.', 'max' => 60],
                            'objective_1_title' => ['label' => 'Tujuan 1: judul', 'default' => 'Memenuhi Kebutuhan', 'max' => 40],
                            'objective_1_text' => ['label' => 'Tujuan 1: uraian', 'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer tincidunt, justo vitae consequat efficitur.', 'type' => 'textarea', 'max' => 250],
                            'objective_2_title' => ['label' => 'Tujuan 2: judul', 'default' => 'Memberikan Layanan', 'max' => 40],
                            'objective_2_text' => ['label' => 'Tujuan 2: uraian', 'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse potenti. Praesent tincidunt mauris non consectetur interdum.', 'type' => 'textarea', 'max' => 250],
                            'objective_3_title' => ['label' => 'Tujuan 3: judul', 'default' => 'Membangun Hubungan', 'max' => 40],
                            'objective_3_text' => ['label' => 'Tujuan 3: uraian', 'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur tincidunt, mauris vitae consequat efficitur.', 'type' => 'textarea', 'max' => 250],
                            'objective_4_title' => ['label' => 'Tujuan 4: judul', 'default' => 'Mengembangkan Usaha', 'max' => 40],
                            'objective_4_text' => ['label' => 'Tujuan 4: uraian', 'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec vitae neque at libero facilisis consequat.', 'type' => 'textarea', 'max' => 250],
                            'objective_5_title' => ['label' => 'Tujuan 5: judul', 'default' => 'Menjaga Kualitas', 'max' => 40],
                            'objective_5_text' => ['label' => 'Tujuan 5: uraian', 'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer tincidunt, justo vitae consequat efficitur.', 'type' => 'textarea', 'max' => 250],
                            'objective_6_title' => ['label' => 'Tujuan 6: judul', 'default' => 'Membuka Peluang', 'max' => 40],
                            'objective_6_text' => ['label' => 'Tujuan 6: uraian', 'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus vitae augue sed neque malesuada consequat.', 'type' => 'textarea', 'max' => 250],
                        ],
                    ],
                    'cta' => [
                        'label' => 'Ajakan lanjutan',
                        'fields' => [
                            'cta_label' => ['label' => 'Label kecil', 'default' => 'Lanjutkan Eksplorasi', 'max' => 40],
                            'cta_title' => ['label' => 'Judul', 'default' => 'Kenali bidang usaha kami.', 'max' => 80],
                            'cta_description' => ['label' => 'Deskripsi', 'default' => 'Lihat berbagai bidang usaha dan layanan PT. Putra Maju Sukses.', 'type' => 'textarea', 'max' => 200],
                            'cta_button' => ['label' => 'Tombol', 'default' => 'Lihat Layanan', 'max' => 30, 'hint' => 'Mengarah ke halaman Layanan & Jasa.'],
                        ],
                    ],
                    'seo' => [
                        'label' => 'Mesin pencari',
                        'fields' => [
                            'seo_description' => ['label' => 'Deskripsi singkat untuk Google', 'default' => 'Mengenal PT. Putra Maju Sukses, profil perusahaan, perjalanan, visi, misi, dan informasi perusahaan.', 'type' => 'textarea', 'max' => 200, 'hint' => 'Tidak tampil di halaman; dipakai mesin pencari.'],
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
        $this->load();

        // Kolom opsional boleh disimpan kosong (artinya disembunyikan); selebihnya kosong = bawaan.
        return array_key_exists($key, $this->stored)
            ? (string) $this->stored[$key]
            : (string) (self::fields($this->page)[$key]['default'] ?? '');
    }

    /**
     * Daftar butir (kolom bertipe list). Disimpan sebagai JSON; bawaan dipakai bila belum diubah.
     *
     * @return list<string>
     */
    public function list(string $key): array
    {
        $this->load();

        $items = isset($this->stored[$key]) ? json_decode($this->stored[$key], true) : null;

        return is_array($items) && $items !== []
            ? array_values(array_filter($items, 'is_string'))
            : (array) (self::fields($this->page)[$key]['default'] ?? []);
    }

    /** URL gambar: unggahan admin bila ada, selain itu gambar bawaan. */
    public function image(string $key): string
    {
        $custom = $this->customImagePath($key);

        return $custom !== null
            ? Storage::disk(config('images.disk'))->url($custom)
            : (string) (self::fields($this->page)[$key]['default'] ?? '');
    }

    /** Path gambar unggahan admin (relatif terhadap disk), atau null bila memakai bawaan. */
    public function customImagePath(string $key): ?string
    {
        $this->load();

        return filled($this->stored[$key] ?? null) ? $this->stored[$key] : null;
    }

    private function load(): void
    {
        $this->stored ??= PageContent::where('page', $this->page)->pluck('value', 'key')->all();
    }

    public function __invoke(string $key): string
    {
        return $this->get($key);
    }
}
