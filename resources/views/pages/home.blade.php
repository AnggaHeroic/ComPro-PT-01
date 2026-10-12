@extends('layouts.app')

@section('title', 'Beranda')

@section('description', 'PT. Putra Maju Sukses merupakan perusahaan yang bergerak dalam berbagai bidang perdagangan dan layanan.')

@section('content')

    {{-- HERO --}}
    <section class="border-b border-slate-200 bg-white">
        <div class="container-site">
            <div class="max-w-6xl pt-16 pb-28 sm:pt-20 sm:pb-32 lg:pt-20 lg:pb-32">
                <p class="font-display text-sm font-semibold uppercase tracking-[0.2em] text-primary">
                    PT. Putra Maju Sukses
                </p>
                <h1
                    class="font-display mt-7 max-w-5xl text-5xl font-bold leading-[1] tracking-tight text-secondary-dark sm:text-6xl lg:text-8xl"
                >
                    Produk dan
                    <span class="text-primary">Solusi</span>
                    untuk Berbagai Kebutuhan Bisnis.
                </h1>

                <div class="mt-10 grid gap-8 lg:grid-cols-2 lg:items-end">
                    <p class="max-w-xl text-base leading-7 text-slate-600 sm:text-lg">
                        PT. Putra Maju Sukses bergerak dalam berbagai
                        bidang perdagangan dan layanan, mencakup
                        teknologi informasi, kebutuhan rumah tangga,
                        olahraga, peralatan industri, konstruksi,
                        dan berbagai kebutuhan lainnya.
                    </p>
                    <div class="flex flex-wrap gap-3 lg:justify-end">
                        <x-button href="{{ route('about') }}">
                            Tentang Kami
                            <x-icons.arrow-right class="h-4 w-4" />
                        </x-button>
                        <x-button
                            href="{{ route('services') }}"
                            variant="secondary"
                        >
                            Layanan & Jasa
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- COMPANY INTRODUCTION --}}
    <section class="bg-surface">
        <div class="container-site">
            <div class="py-24 lg:py-32">

                {{-- Section Label --}}
                <div>
                    <span class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary">
                        Tentang Perusahaan
                    </span>
                </div>

                {{-- Main Heading --}}
                <div class="mt-6 max-w-5xl">
                    <h2
                        class="font-display text-4xl font-bold leading-[1.05] tracking-tight text-secondary-dark sm:text-5xl lg:text-6xl"
                    >
                        <span class="block">
                            Beragam bidang.
                        </span>
                        <span class="block text-primary">
                            Satu perusahaan.
                        </span>
                    </h2>
                </div>

                {{-- Supporting Content --}}
                <div class="mt-12 grid gap-12 lg:grid-cols-12 lg:gap-16">

                    {{-- Description --}}
                    <div class="lg:col-span-7">
                        <p class="max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                            PT. Putra Maju Sukses merupakan perusahaan
                            yang bergerak dalam berbagai bidang perdagangan
                            dan layanan, mulai dari kebutuhan rumah tangga,
                            teknologi informasi, peralatan industri,
                            olahraga, hingga berbagai kebutuhan lainnya.
                        </p>
                        <a
                            href="{{ route('about') }}"
                            class="font-display mt-8 inline-flex items-center gap-2 text-sm font-semibold text-primary transition-all duration-200 hover:gap-3"
                        >
                            Mengenal perusahaan
                            <span>→</span>
                        </a>
                    </div>

                    {{-- Statistic --}}
                    <div class="lg:col-span-3 lg:col-start-10">
                        <div class="border-l-2 border-primary pl-7">
                            <p
                                class="font-display text-7xl font-bold leading-none tracking-tight text-secondary-dark"
                            >
                                14
                            </p>
                            <p class="font-display mt-4 text-base font-semibold leading-6 text-secondary-dark">
                                Bidang usaha
                            </p>
                            <p class="mt-1 max-w-[220px] text-sm leading-6 text-slate-500">
                                dalam berbagai sektor perdagangan dan layanan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SERVICES --}}
    <section class="bg-white">
        <div class="container-site">
            <div class="py-24 lg:py-32">

                {{-- HEADER --}}
                <div class="grid gap-10 lg:grid-cols-12 lg:gap-16">

                    {{-- Left: Heading --}}
                    <div class="lg:col-span-7">
                        <span class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary">
                            Bidang Usaha
                        </span>
                        <h2
                            class="font-display mt-6 max-w-3xl text-4xl font-bold leading-[1.05] tracking-tight text-secondary-dark sm:text-5xl lg:text-6xl"
                        >
                            Beragam produk.
                            <br>
                            <span class="text-primary">
                                Beragam kebutuhan.
                            </span>
                        </h2>
                    </div>

                    {{-- Right: Description + Links --}}
                    <div class="lg:col-span-5 lg:pt-10">
                        <p class="max-w-lg text-base leading-7 text-slate-600">
                            PT. Putra Maju Sukses menyediakan berbagai produk dan
                            layanan untuk mendukung kebutuhan perdagangan,
                            teknologi, industri, dan perlengkapan lainnya.
                        </p>
                        <div class="mt-7 flex flex-col items-start gap-3">
                            <a
                                href="{{ route('services') }}"
                                class="font-display group inline-flex items-center gap-2 text-sm font-semibold text-primary"
                            >
                                Lihat seluruh layanan

                                <span class="transition-transform duration-200 group-hover:translate-x-1">
                                    →
                                </span>
                            </a>
                            <a
                                href="{{ route('contact') }}"
                                class="font-display group inline-flex items-center gap-2 text-sm font-semibold text-secondary-dark"
                            >
                                Hubungi kami

                                <span class="transition-transform duration-200 group-hover:translate-x-1">
                                    →
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- SERVICE GRID --}}
                <div class="mt-16 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">

                    {{-- Technology & Digital --}}
                    <x-service-card
                        number="01"
                        icon="computer"
                        title="Technology & Digital"
                        description="Komputer, piranti lunak, jasa teknologi informasi, dan layanan internet."
                        href="{{ route('services.category', 'technology-digital') }}#technology-digital"
                    />

                    {{-- Home & Lifestyle --}}
                    <x-service-card
                        number="02"
                        icon="house"
                        title="Home & Lifestyle"
                        description="Peralatan masak, dapur, elektronik rumah tangga, perlengkapan rumah, dan mainan anak."
                        href="{{ route('services.category', 'home-lifestyle') }}#home-lifestyle"
                    />

                    {{-- Sports & Music --}}
                    <x-service-card
                        number="03"
                        icon="sports"
                        title="Sports & Music"
                        description="Peralatan dan perlengkapan olahraga serta berbagai alat musik."
                        href="{{ route('services.category', 'sports-music') }}#sports-music"
                    />

                    {{-- Industrial & Machinery --}}
                    <x-service-card
                        number="04"
                        icon="gearcog"
                        title="Industrial & Machinery"
                        description="Mesin, peralatan industri, mesin kantor, suku cadang, dan mesin pendingin."
                        href="{{ route('services.category', 'industrial-machinery') }}#industrial-machinery"
                    />

                    {{-- Construction & Supplies --}}
                    <x-service-card
                        number="05"
                        icon="warehouse"
                        title="Construction & Supplies"
                        description="Bahan konstruksi, alat tulis, alat gambar, dan berbagai perlengkapan lainnya."
                        href="{{ route('services.category', 'construction-supplies') }}#construction-supplies"
                    />
                </div>
            </div>
        </div>
    </section>

    {{-- CONTACT CTA --}}
    <section class="bg-surface">
        <div class="container-site pt-12 pb-16 lg:pt-14 lg:pb-18">
            <div class="mx-auto max-w-6xl border border-slate-300 bg-white">
                <div class="grid lg:grid-cols-12">

                    {{-- MAIN CTA --}}
                    <div class="p-8 sm:p-10 lg:col-span-7 lg:p-11">

                        <span
                            class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary"
                        >
                            Terhubung Dengan Kami
                        </span>

                        <h2
                            class="font-display mt-5 max-w-xl text-4xl font-bold leading-[1.08] tracking-tight text-secondary-dark sm:text-5xl"
                        >
                            Punya kebutuhan bisnis?
                            <span class="text-primary">
                                Mari berdiskusi.
                            </span>
                        </h2>

                        <p
                            class="font-body mt-5 max-w-lg text-sm leading-7 text-slate-600 sm:text-base"
                        >
                            Hubungi PT. Putra Maju Sukses untuk mendapatkan
                            informasi mengenai produk, layanan, dan kebutuhan
                            bisnis Anda.
                        </p>

                        <div class="mt-6">
                            <x-button href="{{ route('contact') }}">
                                Hubungi Kami
                                <x-icons.arrow-right class="h-4 w-4" />
                            </x-button>
                        </div>
                    </div>

                    {{-- CONTACT INFORMATION --}}
                    <div
                        class="border-t border-slate-300 lg:col-span-5 lg:border-l lg:border-t-0"
                    >
                        <div class="p-8 sm:p-10 lg:p-11">
                            <span
                                class="font-display text-xs font-bold uppercase tracking-[0.18em] text-secondary-dark"
                            >
                                Hubungi Kami
                            </span>

                            <div class="mt-5 divide-y divide-slate-200">

                                {{-- PHONE --}}
                                <a
                                    href="tel:081130521216"
                                    class="group flex gap-4 py-3.5 first:pt-0"
                                >
                                    <x-icons.phone
                                        class="h-[18px] w-[18px] shrink-0 text-primary"
                                    />

                                    <div>
                                        <span
                                            class="font-display block text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400"
                                        >
                                            Telepon
                                        </span>

                                        <span
                                            class="font-body mt-1 block text-sm text-secondary-dark transition-colors group-hover:text-primary"
                                        >
                                            081130521216
                                        </span>
                                    </div>
                                </a>

                                {{-- EMAIL --}}
                                <a
                                    href="mailto:sputramaju@gmail.com"
                                    class="group flex gap-4 py-3.5"
                                >
                                    <x-icons.mail
                                        class="h-[18px] w-[18px] shrink-0 text-primary"
                                    />

                                    <div>
                                        <span
                                            class="font-display block text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400"
                                        >
                                            Email
                                        </span>

                                        <span
                                            class="font-body mt-1 block break-all text-sm text-secondary-dark transition-colors group-hover:text-primary"
                                        >
                                            sputramaju@gmail.com
                                        </span>
                                    </div>
                                </a>

                                {{-- ADDRESS --}}
                                <a
                                    href="https://www.google.com/maps/search/?api=1&query={{ urlencode('Jalan Tukad Batanghari Nomor 6, Panjer, Denpasar Selatan, Kota Denpasar') }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group flex gap-4 py-3.5 last:pb-0"
                                    aria-label="Lihat alamat PT. Putra Maju Sukses di Google Maps"
                                >
                                    <x-icons.maps
                                        class="h-[18px] w-[18px] shrink-0 text-primary"
                                    />

                                    <div>
                                        <span
                                            class="font-display block text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400"
                                        >
                                            Alamat
                                        </span>

                                        <span
                                            class="font-body mt-1 block max-w-sm text-sm leading-6 text-secondary-dark transition-colors group-hover:text-primary"
                                        >
                                            Jalan Tukad Batanghari No. 6,
                                            Panjer, Denpasar Selatan,
                                            Kota Denpasar
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection