@extends('layouts.app')

@section('content')

    {{-- Detail Hero --}}
    <section class="bg-white">
        <div class="container-site">
            <div
                class="relative flex min-h-[calc(100svh-5.75rem)] items-center border-b border-slate-200"
            >
                {{-- Back Navigation --}}
                <a
                    href="{{ route('services.category', $category['slug']) }}"
                    aria-label="Kembali ke {{ $category['title'] }}"
                    class="absolute left-0 top-8 z-10 inline-flex h-10 w-10 items-center justify-center text-slate-400 transition-all duration-200 hover:-translate-x-1 hover:text-primary lg:top-10"
                >
                    <x-icons.arrow-left />
                </a>

                <div class="w-full py-20 lg:py-24">

                    {{-- Breadcrumb / Category --}}
                    <div class="flex items-center gap-2">
                        <span
                            class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary"
                        >
                            {{ $category['title'] }}
                        </span>

                        <span
                            class="text-xs text-slate-400"
                            aria-hidden="true"
                        >
                            /
                        </span>

                        <span
                            class="font-display text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                        >
                            Detail
                        </span>
                    </div>

                    {{-- Title --}}
                    <h1
                        class="font-display mt-7 max-w-5xl text-4xl font-bold leading-[1.02] tracking-[-0.045em] text-secondary-dark sm:text-5xl lg:text-7xl"
                    >
                        {{ $activity['title'] }}
                    </h1>

                    {{-- Description --}}
                    <p
                        class="font-body mt-7 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base sm:leading-8"
                    >
                        {{ $activity['description'] }}
                    </p>

                    {{-- Accent --}}
                    <div class="mt-10 h-px w-20 bg-primary"></div>

                </div>
            </div>
        </div>
    </section>


    {{-- Detail Content --}}
    <section class="bg-surface">
        <div class="container-site py-20 sm:py-24 lg:py-28">
            <div class="grid items-center gap-12 lg:grid-cols-12 lg:gap-16">

                {{-- Image --}}
                <div class="lg:col-span-5">
                    <div class="aspect-[4/3] overflow-hidden bg-slate-200">
                        <img
                            src="{{ $activity['image'] }}"
                            alt="{{ $activity['title'] }}"
                            class="h-full w-full object-cover"
                        >
                    </div>
                </div>


                {{-- Content --}}
                <div class="lg:col-span-7">

                    {{-- Section Label --}}
                    <span
                        class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary"
                    >
                        Kegiatan Usaha
                    </span>

                    {{-- Heading --}}
                    <h2
                        class="font-display mt-4 max-w-2xl text-3xl font-bold leading-[1.08] tracking-[-0.035em] text-secondary-dark sm:text-4xl"
                    >
                        Mengenal kegiatan usaha perusahaan.
                    </h2>

                    {{-- Accent --}}
                    <div class="mt-6 h-px w-16 bg-primary"></div>

                    {{-- Description --}}
                    <div class="mt-7 max-w-2xl space-y-4">
                        <p
                            class="font-body text-sm leading-7 text-slate-600 sm:text-base sm:leading-8"
                        >
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                            Integer tincidunt, justo vitae consequat efficitur, neque
                            erat posuere lorem, sed tincidunt erat mauris vitae libero.
                            Suspendisse potenti. Praesent tincidunt mauris non
                            consectetur interdum.
                        </p>

                        <p
                            class="font-body text-sm leading-7 text-slate-600 sm:text-base sm:leading-8"
                        >
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                            Curabitur tincidunt, mauris vitae consequat efficitur.
                            Donec tincidunt, justo vitae consequat efficitur, neque
                            erat posuere lorem.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </section>


    {{-- Documentation --}}
    <section class="bg-white">
        <div
            class="container-site py-20 sm:py-24 lg:py-20"
            x-data="{
                currentPage: 0,

                get pages() {
                    return Math.ceil(
                        {{ count($activity['gallery'] ?? []) }} / 6
                    );
                },

                nextPage() {
                    if (this.currentPage < this.pages - 1) {
                        this.currentPage++;
                    }
                },

                previousPage() {
                    if (this.currentPage > 0) {
                        this.currentPage--;
                    }
                },
            }"
        >

            {{-- Documentation Header --}}
            <div class="border-b border-slate-200 pb-6">

                <div>
                    <span
                        class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary"
                    >
                        Dokumentasi
                    </span>

                    <h2
                        class="font-display mt-3 lg:max-w-none text-3xl font-bold leading-[1.08] tracking-[-0.035em] text-secondary-dark sm:text-4xl"
                    >
                        Kegiatan usaha dalam dokumentasi visual.
                    </h2>
                </div>


                {{-- Gallery Navigation --}}
                <div
                    x-show="pages > 1"
                    x-cloak
                    class="mt-6 flex justify-end gap-2"
                >

                    {{-- Previous Page --}}
                    <button
                        type="button"
                        @click="previousPage()"
                        :disabled="currentPage === 0"
                        aria-label="Dokumentasi sebelumnya"
                        class="inline-flex h-10 w-10 items-center justify-center text-secondary-dark transition-colors duration-200 hover:border-primary hover:text-primary disabled:pointer-events-none disabled:opacity-30"
                    >
                        <x-icons.arrow-left class="h-5 w-5" />
                    </button>


                    {{-- Next Page --}}
                    <button
                        type="button"
                        @click="nextPage()"
                        :disabled="currentPage === pages - 1"
                        aria-label="Dokumentasi berikutnya"
                        class="inline-flex h-10 w-10 items-center justify-center text-secondary-dark transition-colors duration-200 hover:border-primary hover:text-primary disabled:pointer-events-none disabled:opacity-30"
                    >
                        <x-icons.arrow-right class="h-5 w-5" />
                    </button>

                </div>

            </div>


            {{-- Documentation Grid --}}
            <div class="mt-8 overflow-hidden">

                @foreach(array_chunk($activity['gallery'] ?? [], 6) as $pageIndex => $galleryPage)

                    <div
                        x-show="currentPage === {{ $pageIndex }}"
                        x-cloak
                        class="grid grid-cols-2 gap-3 sm:grid-cols-3"
                    >

                        @foreach($galleryPage as $imageIndex => $image)

                            @php
                                $globalIndex = ($pageIndex * 6) + $imageIndex;
                            @endphp

                            <button
                                type="button"
                                @click="$dispatch('documentation-open', { index: {{ $globalIndex }} })"
                                aria-label="Lihat dokumentasi {{ $globalIndex + 1 }}"
                                class="group relative aspect-[4/3] overflow-hidden bg-slate-100 text-left focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                            >
                                <img
                                    src="{{ $image }}"
                                    alt="Dokumentasi {{ $globalIndex + 1 }}"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                >

                                {{-- Hover Overlay --}}
                                <span
                                    class="absolute inset-0 flex items-center justify-center bg-black/0 transition-colors duration-300 group-hover:bg-black/25"
                                >
                                    <span
                                        class="flex h-10 w-10 scale-90 items-center justify-center rounded-full bg-white/90 text-secondary-dark opacity-0 shadow-sm transition-all duration-300 group-hover:scale-100 group-hover:opacity-100"
                                    >
                                        <x-icons.zoom-in class="h-5 w-5" />
                                    </span>
                                </span>
                            </button>

                        @endforeach

                    </div>

                @endforeach

            </div>


            {{-- Image Lightbox --}}
            <x-img-lightbox
                :images="$activity['gallery'] ?? []"
            />

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
                            Butuh informasi lebih lanjut mengenai
                            <br>
                            <span class="text-primary">
                                kegiatan usaha kami?
                            </span>
                        </h2>

                        <p
                            class="font-body mt-4 max-w-2xl text-sm leading-7 text-slate-600"
                        >
                            Hubungi PT. Putra Maju Sukses untuk mendapatkan
                            informasi lebih lanjut mengenai layanan dan aktivitas usaha.
                        </p>

                    </div>


                    <div class="lg:col-span-4 lg:flex lg:justify-end">
                        <x-button
                            href="{{ route('contact') }}"
                            class="w-full sm:w-auto"
                        >
                            Hubungi Kami
                            <x-icons.arrow-right
                                class="h-4 w-4"
                            />
                        </x-button>
                    </div>

                </div>
            </div>
        </div>
    </section>

@endsection