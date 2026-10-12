@extends('layouts.app')

@section('content')

    {{-- HERO --}}
    <section class="bg-white">
        <div class="container-site">
            <div class="border-b border-slate-200 py-20 sm:py-24 lg:py-28">
                <div class="mx-auto max-w-5xl text-center">

                    {{-- Label --}}
                    <span
                        class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary"
                    >
                        Layanan & Jasa
                    </span>

                    {{-- Accent --}}
                    <div
                        class="mx-auto mt-5 h-0.5 w-16 bg-primary"
                        aria-hidden="true"
                    ></div>

                    {{-- Heading --}}
                    <h1
                        class="font-display mt-7 text-[3rem] font-bold leading-[0.98] tracking-[-0.05em] text-secondary-dark sm:text-[3.75rem] lg:text-[4.5rem] xl:text-[4.75rem]"
                    >
                        Beragam bidang usaha.
                        <br>
                        <span class="text-primary">
                            Satu cakupan layanan.
                        </span>
                    </h1>

                    {{-- Description --}}
                    <p
                        class="font-body mx-auto mt-7 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base sm:leading-8"
                    >
                        PT. Putra Maju Sukses memiliki berbagai bidang usaha
                        yang mencakup kebutuhan teknologi, rumah tangga,
                        olahraga, industri, konstruksi, dan berbagai kebutuhan
                        lainnya.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- BUSINESS CATEGORIES --}}
    <section class="bg-surface">
        <div class="container-site py-16 sm:py-20 lg:py-24">
            <div class="max-w-2xl">
                <span class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary">
                    Bidang Usaha
                </span>
                <h2 class="font-display mt-4 text-3xl font-bold tracking-[-0.035em] text-secondary-dark sm:text-4xl">
                    Pilih bidang yang ingin Anda lihat.
                </h2>
                <p class="font-body mt-4 text-sm leading-7 text-slate-600">
                    Jelajahi berbagai bidang usaha dan layanan yang
                    tersedia di PT. Putra Maju Sukses.
                </p>
            </div>

            {{-- CATEGORY GRID --}}
            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <article
                        class="group flex h-full flex-col overflow-hidden border border-slate-300 bg-white transition-all duration-300 hover:border-primary"
                    >
                        {{-- IMAGE --}}
                        <div class="aspect-[16/9] shrink-0 overflow-hidden bg-slate-100">
                            <img
                                src="{{ $category['image'] }}"
                                alt="{{ $category['title'] }}"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                            >
                        </div>

                        {{-- CONTENT --}}
                        <div class="flex min-h-[205px] flex-1 flex-col p-6 sm:p-7">

                            {{-- TITLE --}}
                            <h3
                                class="font-display text-xl font-semibold leading-tight tracking-[-0.02em] text-secondary-dark sm:text-2xl"
                            >
                                {{ $category['title'] }}
                            </h3>

                            {{-- DESCRIPTION --}}
                            <p
                                class="font-body mt-3 line-clamp-3 text-sm leading-6 text-slate-500"
                            >
                                {{ $category['description'] }}
                            </p>

                            {{-- LINK --}}
                            <a
                                href="{{ route('services.category', $category['slug']) }}"
                                class="font-display mt-auto inline-flex items-center gap-2 pt-5 text-sm font-semibold text-primary transition-all duration-200 group-hover:gap-3"
                            >
                                Lihat layanan
                                <span aria-hidden="true">→</span>
                            </a>
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

                    {{-- CTA CONTENT --}}
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
                            <span class="text-primary">
                                lebih lanjut.
                            </span>
                        </h2>
                        <p
                            class="font-body mt-4 max-w-2xl text-sm leading-7 text-slate-600"
                        >
                            Hubungi PT. Putra Maju Sukses untuk mengetahui lebih lanjut
                            mengenai bidang usaha dan layanan yang tersedia.
                        </p>
                    </div>

                    {{-- CTA ACTION --}}
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