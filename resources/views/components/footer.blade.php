<footer class="border-t border-slate-800 bg-secondary-dark text-white">

    {{-- Main Footer --}}
    <div class="container-site py-16 lg:py-20">
        <div class="grid gap-12 lg:grid-cols-12 lg:gap-16">

            {{-- Company + Contact --}}
            <div class="lg:col-span-5">

                {{-- Logo --}}
                <a
                    href="{{ route('home') }}"
                    class="inline-block"
                    aria-label="PT. Putra Maju Sukses - Beranda"
                >
                    <div class="rounded-lg bg-white px-3 py-2">
                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt="PT. Putra Maju Sukses"
                            class="h-10 w-auto"
                        >
                    </div>
                </a>

                {{-- Description --}}
                <p class="mt-6 max-w-md text-sm leading-7 text-slate-400">
                    PT. Putra Maju Sukses merupakan perusahaan yang bergerak
                    dalam berbagai bidang perdagangan dan layanan untuk
                    memenuhi beragam kebutuhan pelanggan dan mitra bisnis.
                </p>

                {{-- Contact Information --}}
                <div class="mt-8 space-y-4">

                    {{-- Phone --}}
                    <a
                        href="tel:081130521216"
                        class="group flex items-center gap-3 text-sm text-slate-400 transition-colors hover:text-white"
                    >
                        <x-icons.phone
                            class="h-[18px] w-[18px] shrink-0 transition-colors group-hover:text-primary"
                        />
                        <span>
                            081130521216
                        </span>
                    </a>

                    {{-- Email --}}
                    <a
                        href="mailto:sputramaju@gmail.com"
                        class="group flex items-center gap-3 text-sm text-slate-400 transition-colors hover:text-white"
                    >
                        <x-icons.mail
                            class="h-[18px] w-[18px] shrink-0 transition-colors group-hover:text-primary"
                        />
                        <span>
                            sputramaju@gmail.com
                        </span>
                    </a>

                    {{-- Address --}}
                    <a
                        href="https://www.google.com/maps/search/?api=1&query={{ urlencode('Jalan Tukad Batanghari Nomor 6, Panjer, Denpasar Selatan, Kota Denpasar') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group flex items-start gap-3 text-sm text-slate-400 transition-colors hover:text-white"
                        aria-label="Lihat alamat PT. Putra Maju Sukses di Google Maps"
                    >
                        <x-icons.maps
                            class="mt-0.5 h-[18px] w-[18px] shrink-0 transition-colors group-hover:text-primary"
                        />
                        <p class="max-w-md leading-6">
                            Jalan Tukad Batanghari No. 6,
                            Panjer, Denpasar Selatan,
                            <span class="block">
                                Kota Denpasar
                            </span>
                        </p>
                    </a>
                </div>
            </div>

            {{-- Navigation --}}
            <div class="lg:col-span-3">
                <h3 class="font-display text-xs font-semibold uppercase tracking-[0.16em] text-white">
                    Navigasi
                </h3>
                <ul class="mt-6 space-y-4">
                    <li>
                        <a
                            href="{{ route('home') }}"
                            class="font-body text-sm text-slate-400 transition hover:text-white"
                        >
                            Beranda
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('about') }}"
                            class="font-body text-sm text-slate-400 transition hover:text-white"
                        >
                            Tentang Kami
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('services') }}"
                            class="font-body text-sm text-slate-400 transition hover:text-white"
                        >
                            Layanan & Jasa
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('contact') }}"
                            class="font-body text-sm text-slate-400 transition hover:text-white"
                        >
                            Kontak Kami
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Business Categories --}}
            <div class="lg:col-span-4">
                <h3 class="font-display text-xs font-semibold uppercase tracking-[0.16em] text-white">
                    Bidang Usaha
                </h3>

                <ul class="mt-6 space-y-4">
                    <li>
                        <a
                            href="{{ route('services.category', 'technology-digital') }}"
                            class="font-body text-sm text-slate-400 transition hover:text-white"
                        >
                            Technology & Digital
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('services.category', 'home-lifestyle') }}"
                            class="font-body text-sm text-slate-400 transition hover:text-white"
                        >
                            Home & Lifestyle
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('services.category', 'sports-music') }}"
                            class="font-body text-sm text-slate-400 transition hover:text-white"
                        >
                            Sports & Music
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('services.category', 'industrial-machinery') }}"
                            class="font-body text-sm text-slate-400 transition hover:text-white"
                        >
                            Industrial & Machinery
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('services.category', 'construction-supplies') }}"
                            class="font-body text-sm text-slate-400 transition hover:text-white"
                        >
                            Construction & Supplies
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Bottom --}}
    <div class="border-t border-white/10">
        <div class="container-site flex flex-col gap-3 py-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="font-body text-xs text-slate-500">
                © {{ date('Y') }} PT. Putra Maju Sukses.
                All Rights Reserved.
            </p>
            <div class="h-px w-16 bg-primary sm:w-20"></div>
        </div>
    </div>
</footer>