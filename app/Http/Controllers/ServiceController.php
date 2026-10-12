<?php

namespace App\Http\Controllers;

class ServiceController extends Controller
{
    public function index()
    {
        $categories = $this->categories();

        return view('services.index', compact('categories'));
    }

    public function category(string $category)
    {
        $categories = $this->categories();

        abort_unless(isset($categories[$category]), 404);

        return view('services.category', [
            'category' => $categories[$category],
        ]);
    }

    public function detail(string $category, string $activity)
    {
        $categories = $this->categories();

        // Pastikan kategori tersedia
        abort_unless(isset($categories[$category]), 404);

        $categoryData = $categories[$category];

        // Cari activity berdasarkan slug
        $activityData = collect($categoryData['activities'])
            ->firstWhere('slug', $activity);

        // Jika activity tidak ditemukan
        abort_unless($activityData, 404);

        return view('services.detail', [
            'category' => $categoryData,
            'activity' => $activityData,
        ]);
    }

    private function categories(): array
    {
        $placeholderImage = 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=85';

        return [

            // =====================================================
            // Technology & Digital
            // =====================================================
            'technology-digital' => [
                'slug' => 'technology-digital',
                'title' => 'Technology & Digital',

                'description' => 'Menyediakan berbagai kebutuhan teknologi informasi, perangkat komputer, piranti lunak, jasa komputer, serta layanan internet.',

                'image' => $placeholderImage,

                'activities' => [
                    [
                        'slug' => 'aktivitas-teknologi-informasi',
                        'title' => 'Aktivitas Teknologi Informasi dan Jasa Komputer Lainnya',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => $placeholderImage,
                    ],

                    [
                        'slug' => 'perdagangan-komputer-dan-perlengkapan',
                        'title' => 'Perdagangan Besar Komputer dan Perlengkapan Komputer',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => $placeholderImage,

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
                        'title' => 'Perdagangan Besar Piranti Lunak',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => $placeholderImage,
                    ],

                    [
                        'slug' => 'internet-service-provider',
                        'title' => 'Internet Service Provider',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => $placeholderImage,
                    ],
                ],
            ],


            // =====================================================
            // Home & Lifestyle
            // =====================================================
            'home-lifestyle' => [
                'slug' => 'home-lifestyle',
                'title' => 'Home & Lifestyle',

                'description' => 'Menyediakan berbagai kebutuhan rumah tangga, peralatan dapur, elektronik rumah tangga, serta produk pendukung kebutuhan keluarga.',

                'image' => $placeholderImage,

                'activities' => [
                    [
                        'slug' => 'peralatan-masak-dapur-dan-elektronik-rumah-tangga',
                        'title' => 'Perdagangan Besar Peralatan Masak, Peralatan Dapur, dan Elektronik Rumah Tangga',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => $placeholderImage,
                    ],

                    [
                        'slug' => 'perlengkapan-rumah-tangga-lainnya',
                        'title' => 'Perdagangan Besar Berbagai Barang dan Perlengkapan Rumah Tangga Lainnya',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => $placeholderImage,
                    ],

                    [
                        'slug' => 'alat-permainan-dan-mainan-anak',
                        'title' => 'Perdagangan Besar Alat Permainan dan Mainan Anak-Anak',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => $placeholderImage,
                    ],
                ],
            ],


            // =====================================================
            // Sports & Music
            // =====================================================
            'sports-music' => [
                'slug' => 'sports-music',
                'title' => 'Sports & Music',

                'description' => 'Menyediakan berbagai peralatan dan perlengkapan olahraga serta alat musik.',

                'image' => $placeholderImage,

                'activities' => [
                    [
                        'slug' => 'peralatan-dan-perlengkapan-olahraga',
                        'title' => 'Perdagangan Besar Peralatan dan Perlengkapan Olahraga',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => $placeholderImage,
                    ],

                    [
                        'slug' => 'alat-musik',
                        'title' => 'Perdagangan Besar Alat Musik',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => $placeholderImage,
                    ],
                ],
            ],


            // =====================================================
            // Industrial & Machinery
            // =====================================================
            'industrial-machinery' => [
                'slug' => 'industrial-machinery',
                'title' => 'Industrial & Machinery',

                'description' => 'Mencakup perdagangan mesin, peralatan dan perlengkapan industri, mesin kantor, suku cadang, serta mesin pendingin.',

                'image' => $placeholderImage,

                'activities' => [
                    [
                        'slug' => 'mesin-peralatan-dan-perlengkapan-lainnya',
                        'title' => 'Perdagangan Besar Mesin, Peralatan dan Perlengkapan Lainnya',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => $placeholderImage,
                    ],

                    [
                        'slug' => 'industri-mesin-pendingin',
                        'title' => 'Industri Mesin Pendingin',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => $placeholderImage,
                    ],

                    [
                        'slug' => 'mesin-kantor-dan-industri-pengolahan',
                        'title' => 'Perdagangan Besar Mesin Kantor dan Industri Pengolahan, Suku Cadang dan Perlengkapannya',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => $placeholderImage,
                    ],
                ],
            ],


            // =====================================================
            // Construction & Supplies
            // =====================================================
            'construction-supplies' => [
                'slug' => 'construction-supplies',
                'title' => 'Construction & Supplies',

                'description' => 'Menyediakan berbagai kebutuhan pendukung konstruksi, alat tulis, alat gambar, dan perlengkapan lainnya.',

                'image' => $placeholderImage,

                'activities' => [
                    [
                        'slug' => 'bahan-konstruksi-lainnya',
                        'title' => 'Perdagangan Besar Bahan Konstruksi Lainnya',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => $placeholderImage,
                    ],

                    [
                        'slug' => 'alat-tulis-dan-gambar',
                        'title' => 'Perdagangan Besar Alat Tulis dan Gambar',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'image' => $placeholderImage,
                    ],
                ],
            ],
        ];
    }
}