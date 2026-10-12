@extends('layouts.app')

@section('content')

    {{-- CATEGORY HERO --}}
    <section class="relative overflow-hidden bg-black">
        <div class="relative min-h-[540px] sm:min-h-[580px] lg:min-h-[600px]">

            {{-- Background Image --}}
            <img
                src="{{ $category['image'] }}"
                alt=""
                class="absolute inset-0 h-full w-full object-cover"
                aria-hidden="true"
            >

            {{-- Black Editorial Gradient --}}
            <div
                class="absolute inset-0 bg-gradient-to-r from-black/95 via-black/55 to-transparent"
                aria-hidden="true"
            ></div>

            {{-- Back Button --}}
            <a
                href="{{ route('services') }}"
                aria-label="Kembali ke Layanan & Jasa"
                class="absolute left-6 top-6 z-20 inline-flex h-10 w-10 items-center justify-center text-white/70 transition-all duration-200 hover:-translate-x-1 hover:text-white sm:left-8 sm:top-8 lg:left-10 lg:top-10"
            >
                <x-icons.arrow-left class="h-4 w-4" />
            </a>

            {{-- Content --}}
            <div
                class="container-site relative z-10 flex min-h-[540px] items-center py-20 sm:min-h-[580px] lg:min-h-[600px]"
            >
                <div class="relative max-w-4xl pl-6 sm:pl-8 lg:pl-10">
                    {{-- Accent Line --}}
                    <div
                        class="absolute bottom-0 left-0 top-0 w-0.5 bg-primary"
                        aria-hidden="true"
                    ></div>

                    {{-- Label --}}
                    <span
                        class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary-vibrant"
                    >
                        Layanan & Jasa
                    </span>

                    {{-- Heading --}}
                    <h1
                        class="font-display mt-5 max-w-3xl text-[3.25rem] font-bold leading-[0.96] tracking-[-0.05em] text-white sm:text-[3.75rem] lg:text-[4.25rem] xl:text-[4.5rem]"
                    >
                        {{ $category['title'] }}
                    </h1>

                    {{-- Description --}}
                    <p
                        class="font-body mt-7 max-w-xl text-sm leading-7 text-white/75 sm:text-base sm:leading-8"
                    >
                        {{ $category['description'] }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- BUSINESS ACTIVITIES --}}
    <section     
        id="{{ $category['slug'] }}"
        class="bg-white"
    >
        <div class="container-site py-16 sm:py-20 lg:py-24">

            {{-- SECTION INTRO --}}
            <div class="max-w-3xl">
                <span
                    class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary"
                >
                    Aktivitas Usaha
                </span>

                <h2
                    class="font-display mt-5 max-w-3xl text-4xl font-bold leading-[1.02] tracking-[-0.045em] text-secondary-dark sm:text-[2.75rem] lg:text-5xl"
                >
                    Layanan dan aktivitas
                    <br>
                    dalam
                    <span class="text-primary">
                        {{ $category['title'] }}.
                    </span>
                </h2>

                <p
                    class="font-body mt-5 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base sm:leading-8"
                >
                    Berikut aktivitas usaha yang termasuk dalam bidang
                    {{ $category['title'] }}.
                </p>
            </div>

            {{-- Activity Cards --}}
            <div class="mt-12 space-y-4 sm:mt-14 lg:mt-16">
                @foreach ($category['activities'] as $activity)
                    <article
                        class="group relative overflow-hidden bg-black/90 sm:h-[380px] lg:h-[390px]"
                    >
                        {{-- Image --}}
                        <img
                            src="{{ $activity['image'] }}"
                            alt="{{ $activity['title'] }}"
                            class="relative h-[220px] w-full object-cover transition-transform duration-700 group-hover:scale-[1.02] sm:absolute sm:inset-0 sm:h-full"
                        >

                        {{-- Information Panel --}}
                        <div
                            @class([
                                'relative w-full sm:absolute sm:inset-y-0 sm:w-[52%] lg:w-[40%]',
                                'sm:left-0' => $loop->even,
                                'sm:right-0' => $loop->odd,
                            ])
                        >
                            {{-- Gradient --}}
                            <div
                                @class([
                                    'absolute inset-0',
                                    'bg-gradient-to-r from-black/88 via-black/86 to-black/68' => $loop->even,
                                    'bg-gradient-to-l from-black/88 via-black/86 to-black/68' => $loop->odd,
                                ])
                            ></div>

                            {{-- Divider --}}
                            @if ($loop->even)
                                {{-- Main divider --}}
                                <div
                                    class="absolute inset-y-0 right-0 w-px bg-white/60"
                                    aria-hidden="true"
                                ></div>

                                {{-- Secondary divider --}}
                                <div
                                    class="absolute inset-y-0 right-2 w-px bg-white/30"
                                    aria-hidden="true"
                                ></div>

                            @else

                                {{-- Main divider --}}
                                <div
                                    class="absolute inset-y-0 left-0 w-px bg-white/60"
                                    aria-hidden="true"
                                ></div>
                                {{-- Secondary divider --}}
                                <div
                                    class="absolute inset-y-0 left-2 w-px bg-white/30"
                                    aria-hidden="true"
                                ></div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div
                            @class([
                                'relative flex min-h-[270px] w-full flex-col px-7 py-7 sm:absolute sm:inset-y-0 sm:w-[52%] sm:px-9 sm:py-8 lg:w-[40%] lg:px-10 lg:py-9',
                                'sm:left-0' => $loop->even,
                                'sm:right-0' => $loop->odd,
                            ])
                        >
                            <div>
                                {{-- Accent line --}}
                                <div
                                    class="h-0.5 w-12 bg-primary"
                                    aria-hidden="true"
                                ></div>

                                {{-- Activity title --}}
                                <h3
                                    class="font-display mt-6 max-w-xl text-xl font-semibold leading-[1.08] tracking-[-0.025em] text-white sm:text-2xl lg:text-[1.65rem]"
                                >
                                    {{ $activity['title'] }}
                                </h3>

                                {{-- Description --}}
                                <p
                                    class="font-body mt-4 line-clamp-3  max-w-xl overflow-hidden text-sm leading-6 text-white/65 sm:text-[15px] sm:leading-7"
                                >
                                    {{ $activity['description'] }}
                                </p>

                            </div>

                            {{-- Bottom Meta --}}
                            <div
                                class="absolute bottom-6 left-7 right-7 sm:bottom-7 sm:left-9 sm:right-9 lg:bottom-8 lg:left-10 lg:right-10"
                            >
                                {{-- Horizontal visual divider --}}
                                <div
                                    class="h-px w-full bg-white/40"
                                    aria-hidden="true"
                                ></div>

                                {{-- Bottom row --}}
                                <div
                                    class="flex items-center justify-between gap-6 pt-3"
                                >
                                    {{-- Category label --}}
                                    <span
                                        class="font-display text-[10px] font-semibold uppercase tracking-[0.16em] text-white/60"
                                    >
                                        Aktivitas Usaha
                                    </span>

                                    {{-- Detail --}}
                                    <a
                                        href="{{ route('services.detail', [
                                            'category' => $category['slug'],
                                            'activity' => $activity['slug'],
                                        ]) }}"
                                        class="font-display shrink-0 text-[10px] font-bold uppercase tracking-[0.18em] text-primary-vibrant transition-colors duration-200 hover:text-white"
                                    >
                                        Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-white">
        <div class="container-site">
            <div class="border-y border-slate-200 py-14 sm:py-16 lg:py-20">
                <div class="grid items-center gap-8 lg:grid-cols-12 lg:gap-12">
                    <div class="lg:col-span-8">
                        <span
                            class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary"
                        >
                            Hubungi Kami
                        </span>

                        <h2
                            class="font-display mt-4 max-w-3xl text-3xl font-bold leading-[1.05] tracking-[-0.04em] text-secondary-dark sm:text-4xl lg:text-[2.5rem]"
                        >
                            Kenali layanan kami
                            <br>
                            <span class="text-primary">
                                lebih lanjut.
                            </span>
                        </h2>
                        <p
                            class="font-body mt-4 max-w-2xl text-sm leading-7 text-slate-600"
                        >
                            Hubungi PT. Putra Maju Sukses untuk mendapatkan
                            informasi mengenai bidang usaha dan layanan yang tersedia.
                        </p>
                    </div>

                    <div class="lg:col-span-4 lg:flex lg:justify-end">
                        <x-button
                            href="{{ route('contact') }}"
                            class="w-full sm:w-auto"
                        >
                            Hubungi Kami
                            <span aria-hidden="true">→</span>
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection