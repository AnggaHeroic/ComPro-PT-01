<?php

namespace Database\Seeders;

use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use App\Support\CategoryIcons;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    /**
     * Data awal dipindahkan dari array statis ServiceController.
     * Idempoten: dicocokkan berdasarkan slug dan hanya MEMBUAT data yang belum ada,
     * sehingga perubahan admin lewat CMS tidak tertimpa saat seeder dijalankan ulang.
     * kbli_code sengaja dikosongkan sampai kode resmi terverifikasi.
     */
    public function run(): void
    {
        foreach ($this->catalog() as $categoryOrder => $data) {
            $category = ServiceCategory::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'image_path' => $data['image'],
                    'icon' => CategoryIcons::forSlug($data['slug']),
                    'sort_order' => $categoryOrder + 1,
                    'is_published' => true,
                ],
            );

            foreach ($data['activities'] as $activityOrder => $item) {
                $activity = BusinessActivity::firstOrCreate(
                    [
                        'service_category_id' => $category->id,
                        'slug' => $item['slug'],
                    ],
                    [
                        'name' => $item['name'],
                        'short_description' => $item['description'],
                        'description' => $item['description'],
                        'image_path' => $item['image'],
                        'sort_order' => $activityOrder + 1,
                        'is_published' => true,
                    ],
                );

                if ($activity->wasRecentlyCreated) {
                    foreach ($item['gallery'] ?? [] as $galleryOrder => $url) {
                        $activity->images()->create([
                            'image_path' => $url,
                            'sort_order' => $galleryOrder + 1,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function catalog(): array
    {
        return [
            [
                'slug' => 'technology-digital',
                'name' => 'Technology & Digital',
                'description' => 'Menyediakan berbagai kebutuhan teknologi informasi, perangkat komputer, piranti lunak, jasa komputer, serta layanan internet.',
                'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                'activities' => [
                    [
                        'slug' => 'aktivitas-teknologi-informasi',
                        'name' => 'Aktivitas Teknologi Informasi dan Jasa Komputer Lainnya',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                    ],
                    [
                        'slug' => 'perdagangan-komputer-dan-perlengkapan',
                        'name' => 'Perdagangan Besar Komputer dan Perlengkapan Komputer',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                        'gallery' => [
                            'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1200&q=85',
                            'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&q=85',
                            'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=85',
                            'https://images.unsplash.com/photo-1497366412874-3415097a27e7?auto=format&fit=crop&w=1200&q=85',
                            'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1200&q=85',
                            'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&q=85',
                            'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=85',
                            'https://images.unsplash.com/photo-1497366412874-3415097a27e7?auto=format&fit=crop&w=1200&q=85',
                        ],
                    ],
                    [
                        'slug' => 'perdagangan-piranti-lunak',
                        'name' => 'Perdagangan Besar Piranti Lunak',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                    ],
                    [
                        'slug' => 'internet-service-provider',
                        'name' => 'Internet Service Provider',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                    ],
                ],
            ],
            [
                'slug' => 'home-lifestyle',
                'name' => 'Home & Lifestyle',
                'description' => 'Menyediakan berbagai kebutuhan rumah tangga, peralatan dapur, elektronik rumah tangga, serta produk pendukung kebutuhan keluarga.',
                'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                'activities' => [
                    [
                        'slug' => 'peralatan-masak-dapur-dan-elektronik-rumah-tangga',
                        'name' => 'Perdagangan Besar Peralatan Masak, Peralatan Dapur, dan Elektronik Rumah Tangga',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                    ],
                    [
                        'slug' => 'perlengkapan-rumah-tangga-lainnya',
                        'name' => 'Perdagangan Besar Berbagai Barang dan Perlengkapan Rumah Tangga Lainnya',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                    ],
                    [
                        'slug' => 'alat-permainan-dan-mainan-anak',
                        'name' => 'Perdagangan Besar Alat Permainan dan Mainan Anak-Anak',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                    ],
                ],
            ],
            [
                'slug' => 'sports-music',
                'name' => 'Sports & Music',
                'description' => 'Menyediakan berbagai peralatan dan perlengkapan olahraga serta alat musik.',
                'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                'activities' => [
                    [
                        'slug' => 'peralatan-dan-perlengkapan-olahraga',
                        'name' => 'Perdagangan Besar Peralatan dan Perlengkapan Olahraga',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                    ],
                    [
                        'slug' => 'alat-musik',
                        'name' => 'Perdagangan Besar Alat Musik',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                    ],
                ],
            ],
            [
                'slug' => 'industrial-machinery',
                'name' => 'Industrial & Machinery',
                'description' => 'Mencakup perdagangan mesin, peralatan dan perlengkapan industri, mesin kantor, suku cadang, serta mesin pendingin.',
                'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                'activities' => [
                    [
                        'slug' => 'mesin-peralatan-dan-perlengkapan-lainnya',
                        'name' => 'Perdagangan Besar Mesin, Peralatan dan Perlengkapan Lainnya',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                    ],
                    [
                        'slug' => 'industri-mesin-pendingin',
                        'name' => 'Industri Mesin Pendingin',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                    ],
                    [
                        'slug' => 'mesin-kantor-dan-industri-pengolahan',
                        'name' => 'Perdagangan Besar Mesin Kantor dan Industri Pengolahan, Suku Cadang dan Perlengkapannya',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                    ],
                ],
            ],
            [
                'slug' => 'construction-supplies',
                'name' => 'Construction & Supplies',
                'description' => 'Menyediakan berbagai kebutuhan pendukung konstruksi, alat tulis, alat gambar, dan perlengkapan lainnya.',
                'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                'activities' => [
                    [
                        'slug' => 'bahan-konstruksi-lainnya',
                        'name' => 'Perdagangan Besar Bahan Konstruksi Lainnya',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                    ],
                    [
                        'slug' => 'alat-tulis-dan-gambar',
                        'name' => 'Perdagangan Besar Alat Tulis dan Gambar',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85',
                    ],
                ],
            ],
        ];
    }
}
