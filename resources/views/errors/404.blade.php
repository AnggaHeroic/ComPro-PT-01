@extends('layouts.app')

@section('title', 'Halaman Tidak Ditemukan')

@section('description', 'Halaman yang Anda cari tidak ditemukan.')

@section('content')

    <section class="bg-white">
        <div class="container-site">
            <div
                class="min-h-[calc(100dvh+2rem)] py-24 lg:py-20"
            >
                <div
                    class="grid w-full items-center gap-16 lg:grid-cols-12 lg:gap-20"
                >

                    {{-- ERROR MESSAGE --}}
                    <div class="lg:col-span-7">
                        <span
                            class="font-display text-xs font-bold uppercase tracking-[0.18em] text-primary"
                        >
                            Error 404
                        </span>

                        {{-- Main Heading --}}
                        <h1
                            class="font-display mt-6 max-w-3xl text-5xl font-bold leading-[1.02] tracking-tight text-secondary-dark sm:text-6xl xl:text-[5rem]"
                        >
                            <span class="block">
                                Halaman yang
                            </span>

                            <span class="block">
                                Anda cari
                            </span>

                            <span class="block text-primary">
                                tidak ditemukan.
                            </span>
                        </h1>

                        {{-- Description --}}
                        <p
                            class="font-body mt-7 max-w-xl text-sm leading-7 text-slate-600 sm:text-base"
                        >
                            Halaman ini mungkin sudah dipindahkan,
                            dihapus, atau alamat yang Anda masukkan
                            tidak sesuai.
                        </p>

                        {{-- Actions --}}
                        <div class="mt-8 flex flex-wrap gap-3">

                            {{-- Back to Home --}}
                            <x-button href="{{ route('home') }}">
                                Kembali ke Beranda
                                <x-icons.arrow-right class="h-4 w-4" />
                            </x-button>

                            {{-- Contact --}}
                            <x-button
                                href="{{ route('contact') }}"
                                variant="secondary"
                            >
                                Hubungi Kami
                            </x-button>
                        </div>
                    </div>

                    {{-- ERROR VISUAL --}}
                    <div
                        class="lg:col-span-5 lg:-translate-y-8"
                    >
                        <div
                            class="border-l border-slate-200 pl-8 lg:pl-12"
                        >

                            {{-- Custom 404 Graphic --}}
                            <div
                                class="w-full max-w-[620px] text-primary"
                            >
                                <x-icons.error-404
                                    class="h-auto w-full"
                                />
                            </div>

                            {{-- Supporting Information --}}
                            <div class="mt-7">
                                <p
                                    class="font-display text-[11px] font-bold uppercase tracking-[0.2em] text-secondary-dark"
                                >
                                    Halaman Tidak Tersedia
                                </p>

                                <p
                                    class="font-body mt-2 max-w-xs text-sm leading-6 text-slate-500"
                                >
                                    Silakan kembali ke halaman utama
                                    untuk melanjutkan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection