@php
    // Footer menyesuaikan diri: <= 6 kategori satu kolom; 7-12 dua kolom;
    // lebih dari 12 hanya 12 pertama + tautan ke semua bidang usaha.
    $manyCategories = $navCategories->count() > 6;
    $footerCategories = $navCategories->take(12);
    $hiddenCategories = max(0, $navCategories->count() - 12);
@endphp

<footer{!! $siteInfo->mark('identity') !!} class="border-t border-slate-800 bg-secondary-dark text-white">

    {{-- Main Footer --}}
    <div class="container-site py-16 lg:py-20">
        <div class="grid gap-12 lg:grid-cols-12 lg:gap-16">

            {{-- Company + Contact --}}
            <div @class(['lg:col-span-5' => ! $manyCategories, 'lg:col-span-4' => $manyCategories])>

                {{-- Logo --}}
                <a
                    href="{{ route('home') }}"
                    class="inline-block"
                    aria-label="PT. Putra Maju Sukses - Beranda"
                >
                    <div class="rounded-lg bg-white px-3 py-2">
                        <img
                            src="{{ $siteInfo->image('company_logo') }}"
                            alt="{{ $siteInfo('company_logo_alt') }}"
                            class="h-10 w-auto"
                        >
                    </div>
                </a>

                {{-- Description --}}
                <p class="mt-6 max-w-md text-sm leading-7 text-justify text-slate-400">
                    {{ $siteInfo('footer_description') }}
                </p>

                {{-- Contact Information --}}
                <div class="mt-8 space-y-4">

                    {{-- Phone --}}
                    <a
                        href="{{ \App\Support\PageContents::telHref($siteInfo('contact_phone')) }}"
                        class="group flex items-center gap-3 text-sm text-slate-400 transition-colors hover:text-white"
                    >
                        <x-icons.phone
                            class="h-[18px] w-[18px] shrink-0 transition-colors group-hover:text-primary"
                        />
                        <span>
                            {{ $siteInfo('contact_phone') }}
                        </span>
                    </a>

                    {{-- Email --}}
                    <a
                        href="mailto:{{ $siteInfo('contact_email') }}"
                        class="group flex items-center gap-3 text-sm text-slate-400 transition-colors hover:text-white"
                    >
                        <x-icons.mail
                            class="h-[18px] w-[18px] shrink-0 transition-colors group-hover:text-primary"
                        />
                        <span>
                            {{ $siteInfo('contact_email') }}
                        </span>
                    </a>

                    {{-- Address --}}
                    <a
                        href="{{ \App\Support\PageContents::mapsSearchUrl($siteInfo('contact_address')) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group flex items-start gap-3 text-sm text-slate-400 transition-colors hover:text-white"
                        aria-label="Lihat alamat PT. Putra Maju Sukses di Google Maps"
                    >
                        <x-icons.maps
                            class="mt-0.5 h-[18px] w-[18px] shrink-0 transition-colors group-hover:text-primary"
                        />
                        <p class="max-w-md leading-6">
                            {{ $siteInfo('contact_address') }}
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
            <div @class(['lg:col-span-4' => ! $manyCategories, 'lg:col-span-5' => $manyCategories])>
                <h3 class="font-display text-xs font-semibold uppercase tracking-[0.16em] text-white">
                    Bidang Usaha
                </h3>

                {{-- <= 6: satu kolom polos. > 6: dua kolom yang dibaca turun ke bawah, bernomor, dengan garis pemisah halus. --}}
                <ul @class([
                    'mt-6',
                    'space-y-4' => ! $manyCategories,
                    'columns-1 gap-x-10 sm:columns-2 [column-rule:1px_solid_rgb(255_255_255/0.08)]' => $manyCategories,
                ])>
                    @foreach ($footerCategories as $navCategory)
                        <li @if ($manyCategories) class="break-inside-avoid pb-3.5" @endif>
                            <a
                                href="{{ route('services.category', $navCategory->slug) }}"
                                @class([
                                    'font-body text-sm text-slate-400 transition hover:text-white',
                                    'group flex items-baseline gap-3 leading-snug' => $manyCategories,
                                ])
                            >
                                @if ($manyCategories)
                                    <span class="font-display w-5 shrink-0 text-[11px] font-bold text-slate-500 transition-colors group-hover:text-primary" aria-hidden="true">
                                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span>{{ $navCategory->name }}</span>
                                @else
                                    {{ $navCategory->name }}
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if ($hiddenCategories > 0)
                    <a
                        href="{{ route('services') }}"
                        class="font-display group mt-4 inline-flex items-center gap-2 border border-white/20 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:border-primary"
                    >
                        Lihat semua {{ $navCategories->count() }} bidang usaha
                        <span class="transition-transform duration-200 group-hover:translate-x-1" aria-hidden="true">→</span>
                    </a>
                @endif
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