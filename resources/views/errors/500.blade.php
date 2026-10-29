@extends(\App\Support\ErrorLayout::name())

@section('title', 'Terjadi Kesalahan Server')

@section('description', 'Terjadi kesalahan pada server. Silakan coba lagi beberapa saat lagi.')

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
                            Error 500
                        </span>

                        {{-- Main Heading --}}
                        <h1
                            class="font-display mt-6 max-w-3xl text-5xl font-bold leading-[1.02] tracking-tight text-secondary-dark sm:text-6xl xl:text-[5rem]"
                        >
                            <span class="block">
                                Terjadi kesalahan
                            </span>

                            <span class="block">
                                pada server
                            </span>

                            <span class="block text-primary">
                                kami.
                            </span>
                        </h1>

                        {{-- Description --}}
                        <p
                            class="font-body mt-7 max-w-xl text-sm leading-7 text-slate-600 sm:text-base"
                        >
                            Maaf, halaman ini sedang bermasalah. Silakan muat ulang
                            halaman atau coba lagi beberapa saat lagi.
                        </p>

                        {{-- Actions --}}
                        <div class="mt-8 flex flex-wrap gap-3">

                            {{-- Back to Home --}}
                            <x-button href="{{ url('/') }}">
                                Kembali ke Beranda
                                <x-icons.arrow-right class="h-4 w-4" />
                            </x-button>

                            {{-- Retry --}}
                            <x-button
                                href="{{ url()->current() }}"
                                variant="secondary"
                            >
                                Coba Lagi
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

                            {{-- Custom 500 Graphic --}}
                            <div
                                class="w-full max-w-[620px] text-primary"
                            >
                                <x-icons.error-500
                                    class="h-auto w-full"
                                />
                            </div>

                            {{-- Supporting Information --}}
                            <div class="mt-7">
                                <p
                                    class="font-display text-[11px] font-bold uppercase tracking-[0.2em] text-secondary-dark"
                                >
                                    Gangguan Sementara
                                </p>

                                <p
                                    class="font-body mt-2 max-w-xs text-sm leading-6 text-slate-500"
                                >
                                    Silakan kembali ke halaman utama atau coba lagi nanti.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection