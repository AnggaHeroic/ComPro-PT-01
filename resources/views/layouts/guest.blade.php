<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>Masuk — Admin {{ config('app.name') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white text-gray-900">
        <div class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">

            {{-- Brand panel --}}
            <aside class="relative hidden overflow-hidden bg-primary-dark text-white lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16">
                {{-- Decorative layers --}}
                <div class="pointer-events-none absolute inset-0 bg-linear-to-br from-primary via-primary-dark to-[#6e0f0e]"></div>
                <div
                    class="pointer-events-none absolute inset-0 opacity-[0.12]"
                    style="background-image: linear-gradient(to right, #fff 1px, transparent 1px), linear-gradient(to bottom, #fff 1px, transparent 1px); background-size: 48px 48px; mask-image: radial-gradient(ellipse at 30% 30%, #000 10%, transparent 75%); -webkit-mask-image: radial-gradient(ellipse at 30% 30%, #000 10%, transparent 75%);"
                ></div>
                <x-icons.house class="pointer-events-none absolute -bottom-16 -right-16 size-md text-white/[0.07]" stroke-width="0.5" />

                <div class="relative">
                    <a href="{{ route('home') }}" class="inline-flex items-center rounded bg-white p-2" aria-label="Kembali ke website">
                        <x-application-logo class="h-10 w-auto" />
                    </a>
                </div>

                <div class="relative max-w-md">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/70">Panel Admin</p>
                    <h2 class="mt-3 font-display text-3xl font-bold leading-tight xl:text-4xl">
                        Kelola konten website dengan mudah.
                    </h2>
                    <p class="mt-4 text-sm leading-relaxed text-white/75">
                        Perbarui kategori layanan, aktivitas usaha, galeri, dan informasi perusahaan tanpa menyentuh kode.
                    </p>

                    {{-- Dekorasi: mockup abstrak panel admin --}}
                    <div class="relative mt-10" aria-hidden="true">
                        <div class="overflow-hidden rounded border border-white/20 bg-white/10 backdrop-blur-sm">
                            {{-- Window bar --}}
                            <div class="flex items-center gap-1.5 border-b border-white/15 px-3 py-2.5">
                                <span class="h-2 w-2 rounded-full bg-white/40"></span>
                                <span class="h-2 w-2 rounded-full bg-white/30"></span>
                                <span class="h-2 w-2 rounded-full bg-white/20"></span>
                                <span class="ms-3 h-2 w-24 rounded-full bg-white/15"></span>
                            </div>

                            <div class="flex">
                                {{-- Sidebar --}}
                                <div class="w-14 shrink-0 space-y-2.5 border-r border-white/15 p-3">
                                    <span class="block h-2 rounded-full bg-white/60"></span>
                                    <span class="block h-2 rounded-full bg-white/20"></span>
                                    <span class="block h-2 rounded-full bg-white/20"></span>
                                    <span class="block h-2 rounded-full bg-white/20"></span>
                                </div>

                                {{-- Content --}}
                                <div class="flex-1 space-y-3 p-4">
                                    <span class="block h-2.5 w-1/3 rounded-full bg-white/50"></span>

                                    <div class="grid grid-cols-3 gap-2.5">
                                        <div class="space-y-2 rounded-sm border border-white/15 bg-white/5 p-2.5">
                                            <x-icons.computer class="h-4 w-4 text-white/70" />
                                            <span class="block h-1.5 w-2/3 rounded-full bg-white/30"></span>
                                        </div>
                                        <div class="space-y-2 rounded-sm border border-white/15 bg-white/5 p-2.5">
                                            <x-icons.warehouse class="h-4 w-4 text-white/70" />
                                            <span class="block h-1.5 w-2/3 rounded-full bg-white/30"></span>
                                        </div>
                                        <div class="space-y-2 rounded-sm border border-white/15 bg-white/5 p-2.5">
                                            <x-icons.gearcog class="h-4 w-4 text-white/70" />
                                            <span class="block h-1.5 w-2/3 rounded-full bg-white/30"></span>
                                        </div>
                                    </div>

                                    <div class="space-y-2 rounded-sm border border-white/15 bg-white/5 p-3">
                                        <span class="block h-1.5 w-full rounded-full bg-white/25"></span>
                                        <span class="block h-1.5 w-5/6 rounded-full bg-white/20"></span>
                                        <span class="block h-1.5 w-2/3 rounded-full bg-white/15"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Floating gallery card --}}
                        <div class="absolute -bottom-6 -right-4 w-36 rounded border border-white/25 bg-primary-dark/80 p-2.5 backdrop-blur-md">
                            <div class="grid grid-cols-2 gap-1.5">
                                <span class="flex h-9 items-center justify-center rounded-sm bg-white/20 text-white/80">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="1.5"/><path d="M21 16l-5-5-8 9"/></svg>
                                </span>
                                <span class="flex h-9 items-center justify-center rounded-sm bg-white/10 text-white/60">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="1.5"/><path d="M21 16l-5-5-8 9"/></svg>
                                </span>
                            </div>
                            <span class="mt-2 block h-1.5 w-2/3 rounded-full bg-white/40"></span>
                        </div>
                    </div>
                </div>

                <p class="relative text-xs text-white/50">&copy; {{ date('Y') }} PT. Putra Maju Sukses</p>
            </aside>

            {{-- Form panel --}}
            <main class="flex flex-col justify-center bg-surface px-6 py-10 sm:px-12">
                <div class="mx-auto w-full max-w-sm">
                    <a href="{{ route('home') }}" class="mb-10 inline-block lg:hidden" aria-label="Kembali ke website">
                        <x-application-logo class="h-14 w-auto mix-blend-multiply" />
                    </a>

                    {{ $slot }}

                    <a href="{{ route('home') }}" class="mt-8 inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-primary">
                        <x-icons.arrow-left class="h-4 w-4" />
                        Kembali ke website
                    </a>
                </div>
            </main>
        </div>
    </body>
</html>
