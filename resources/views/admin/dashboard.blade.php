<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Selamat datang kembali, {{ auth()->user()->name }}.</p>
    </x-slot>

    <x-slot name="actions">
        <x-secondary-button size="sm" :href="route('home')" target="_blank" rel="noopener">Lihat website
            <x-admin.arrow direction="external" class="h-3.5 w-3.5" />
        </x-secondary-button>
    </x-slot>

    {{-- Banner: elemen visual yang sama dengan halaman login --}}
    <section class="relative isolate mb-8 overflow-hidden rounded bg-linear-to-br from-primary via-primary-dark to-[#6e0f0e] px-7 py-8 text-white sm:px-10 sm:py-10 lg:py-10">
        <div class="pointer-events-none absolute inset-0 -z-10 opacity-[0.12]" style="background-image: linear-gradient(to right, #fff 1px, transparent 1px), linear-gradient(to bottom, #fff 1px, transparent 1px); background-size: 40px 40px; background-size: 48px 48px; mask-image: radial-gradient(ellipse at 20% 20%, #000 10%, transparent 75%); -webkit-mask-image: radial-gradient(ellipse at 20% 20%, #000 10%, transparent 75%);"></div>

        {{-- Ilustrasi dekoratif: halaman website yang sedang diedit lewat panel (hanya layar lebar, xl ke atas) --}}
        <div class="pointer-events-none absolute right-12 top-1/2 hidden w-64 -translate-y-1/2 xl:block" aria-hidden="true">
            <svg viewBox="0 0 320 230" fill="none" class="h-auto w-full drop-shadow-[0_14px_24px_rgba(0,0,0,0.28)]">
                {{-- Jendela browser --}}
                <rect x="8" y="10" width="262" height="176" rx="9" fill="#fff" fill-opacity="0.1" stroke="#fff" stroke-opacity="0.5" stroke-width="2"/>
                <path d="M8 38h262" stroke="#fff" stroke-opacity="0.25"/>
                <circle cx="22" cy="24" r="3" fill="#fff" fill-opacity="0.65"/>
                <circle cx="33" cy="24" r="3" fill="#fff" fill-opacity="0.4"/>
                <circle cx="44" cy="24" r="3" fill="#fff" fill-opacity="0.25"/>
                <rect x="62" y="19" width="120" height="10" rx="5" fill="#fff" fill-opacity="0.2"/>

                {{-- Hero halaman --}}
                <rect x="22" y="50" width="234" height="52" rx="5" fill="#fff" fill-opacity="0.16"/>
                <rect x="34" y="62" width="96" height="8" rx="4" fill="#fff" fill-opacity="0.9"/>
                <rect x="34" y="76" width="64" height="5" rx="2.5" fill="#fff" fill-opacity="0.45"/>
                <rect x="34" y="88" width="34" height="8" rx="2" fill="#fff" fill-opacity="0.95"/>
                <rect x="176" y="58" width="68" height="36" rx="3" fill="#fff" fill-opacity="0.16"/>
                <path d="M182 88l16-17 12 11 8-7 20 13" stroke="#fff" stroke-opacity="0.85" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="226" cy="68" r="4" fill="#fff" fill-opacity="0.7"/>

                {{-- Kartu layanan --}}
                <rect x="22" y="112" width="72" height="62" rx="4" fill="#fff" fill-opacity="0.1" stroke="#fff" stroke-opacity="0.35"/>
                <rect x="32" y="122" width="14" height="14" rx="3" fill="#fff" fill-opacity="0.55"/>
                <rect x="32" y="144" width="46" height="5" rx="2.5" fill="#fff" fill-opacity="0.8"/>
                <rect x="32" y="155" width="34" height="4" rx="2" fill="#fff" fill-opacity="0.35"/>
                <rect x="104" y="112" width="72" height="62" rx="4" fill="#fff" fill-opacity="0.1" stroke="#fff" stroke-opacity="0.35"/>
                <rect x="114" y="122" width="14" height="14" rx="3" fill="#fff" fill-opacity="0.55"/>
                <rect x="114" y="144" width="46" height="5" rx="2.5" fill="#fff" fill-opacity="0.8"/>
                <rect x="114" y="155" width="34" height="4" rx="2" fill="#fff" fill-opacity="0.35"/>
                {{-- Kartu yang sedang diedit --}}
                <rect x="186" y="112" width="72" height="62" rx="4" fill="#fff" fill-opacity="0.18" stroke="#fff" stroke-opacity="0.9" stroke-dasharray="4 3"/>
                <rect x="196" y="122" width="14" height="14" rx="3" fill="#fff" fill-opacity="0.55"/>
                <rect x="196" y="144" width="46" height="5" rx="2.5" fill="#fff" fill-opacity="0.8"/>
                <rect x="196" y="155" width="34" height="4" rx="2" fill="#fff" fill-opacity="0.35"/>
                <circle cx="256" cy="112" r="10" fill="#fff"/>
                <path d="M251.5 117l.9-3.4 6.4-6.4 2.5 2.5-6.4 6.4z" fill="#cb1e1b"/>

                {{-- Panel edit (formulir) --}}
                <rect x="190" y="134" width="124" height="88" rx="8" fill="#fff" fill-opacity="0.97"/>
                <rect x="200" y="144" width="52" height="7" rx="3.5" fill="#374151"/>
                <rect x="200" y="158" width="104" height="12" rx="3" fill="#f3f4f6" stroke="#d1d5db"/>
                <rect x="205" y="162" width="46" height="4" rx="2" fill="#9ca3af"/>
                <rect x="200" y="176" width="104" height="12" rx="3" fill="#f3f4f6" stroke="#d1d5db"/>
                <rect x="205" y="180" width="62" height="4" rx="2" fill="#9ca3af"/>

                {{-- Pilihan status: terbit (hijau), draft (kuning), simpan (merah) --}}
                <rect x="200" y="196" width="34" height="12" rx="6" fill="#dcfce7"/>
                <circle cx="208" cy="202" r="3" fill="#16a34a"/>
                <rect x="214" y="200" width="16" height="4" rx="2" fill="#16a34a"/>
                <rect x="238" y="196" width="34" height="12" rx="6" fill="#fef9c3"/>
                <circle cx="246" cy="202" r="3" fill="#ca8a04"/>
                <rect x="252" y="200" width="16" height="4" rx="2" fill="#ca8a04"/>
                <rect x="278" y="196" width="26" height="12" rx="3" fill="#cb1e1b"/>
            </svg>
        </div>

        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/70">Panel Admin</p>
        <h2 class="mt-2 max-w-xl font-display text-2xl font-bold leading-tight sm:text-3xl">Kelola konten website dengan mudah.</h2>
        <p class="mt-3 max-w-xl text-sm leading-relaxed text-white/75">Perbarui kategori layanan, aktivitas usaha, dan galeri tanpa menyentuh kode.</p>

        <div class="mt-6 grid grid-cols-2 gap-3 sm:flex sm:flex-wrap">
            <a href="{{ route('admin.categories.create') }}" class="col-span-2 inline-flex items-center justify-center rounded bg-white px-4 py-2.5 text-sm font-semibold text-primary-dark transition hover:bg-primary-light sm:col-auto">+ Tambah kategori</a>
            <a href="{{ route('admin.activities.create') }}" class="col-span-2 inline-flex items-center justify-center rounded bg-white px-4 py-2.5 text-sm font-semibold text-primary-dark transition hover:bg-primary-light sm:col-auto">+ Tambah aktivitas</a>
            <a href="{{ route('admin.gallery.index') }}" class="inline-flex items-center justify-center rounded border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">Kelola galeri</a>
            <a href="{{ route('admin.profile.edit') }}" class="inline-flex items-center justify-center rounded border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">Profil akun</a>
        </div>
    </section>

    {{-- Ringkasan --}}
    <div class="grid gap-5 md:grid-cols-3">
        <a href="{{ route('admin.categories.index') }}" class="group relative overflow-hidden rounded border border-gray-200 bg-white p-6 shadow-sm transition hover:border-primary/40">
            <span class="absolute inset-x-0 top-0 h-1 bg-primary"></span>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Kategori</p>
                    <p class="mt-3 font-display text-4xl font-bold text-gray-900">{{ $categoryCount }}</p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded bg-primary-light text-primary">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 12l9 4 9-4"/><path d="M3 17l9 4 9-4"/></svg>
                </span>
            </div>
            <p class="mt-3 text-sm text-gray-500">{{ $categoryPublished }} terbit, {{ $categoryCount - $categoryPublished }} draft</p>
            <p class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-primary">Kelola kategori <x-admin.arrow class="transition group-hover:translate-x-1" /></p>
        </a>

        <a href="{{ route('admin.activities.index') }}" class="group relative overflow-hidden rounded border border-gray-200 bg-white p-6 shadow-sm transition hover:border-primary/40">
            <span class="absolute inset-x-0 top-0 h-1 bg-primary"></span>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Aktivitas Usaha</p>
                    <p class="mt-3 font-display text-4xl font-bold text-gray-900">{{ $activityCount }}</p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded bg-primary-light text-primary">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/></svg>
                </span>
            </div>
            <p class="mt-3 text-sm text-gray-500">{{ $activityPublished }} terbit, {{ $activityCount - $activityPublished }} draft</p>
            <p class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-primary">Kelola aktivitas <x-admin.arrow class="transition group-hover:translate-x-1" /></p>
        </a>

        <a href="{{ route('admin.gallery.index') }}" class="group relative overflow-hidden rounded border border-gray-200 bg-white p-6 shadow-sm transition hover:border-primary/40">
            <span class="absolute inset-x-0 top-0 h-1 bg-primary"></span>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Foto Galeri</p>
                    <p class="mt-3 font-display text-4xl font-bold text-gray-900">{{ $photoCount }}</p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded bg-primary-light text-primary">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="1.5"/><path d="M21 16l-5-5-8 9"/></svg>
                </span>
            </div>
            <p class="mt-3 text-sm text-gray-500">di {{ $activitiesWithGallery }} dari {{ $activityCount }} aktivitas</p>
            <p class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-primary">Kelola galeri <x-admin.arrow class="transition group-hover:translate-x-1" /></p>
        </a>
    </div>

    {{-- Kelola konten halaman: kartu per halaman; jarak, sudut, dan bayangan mengikuti tiga kartu ringkasan di atasnya --}}
    @php
        $pageIcons = [
            'home' => 'M3 10.5 12 3l9 7.5M5 9.5V21h14V9.5M9 21v-6h6v6',
            'about' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-8 9c0-4 4-6 8-6s8 2 8 6',
            'services' => 'M3 7l9-4 9 4-9 4-9-4zM3 12l9 4 9-4M3 17l9 4 9-4',
            'contact' => 'M4 6h16v12H4zM4 7l8 6 8-6',
        ];
    @endphp

    <section class="mt-8" aria-labelledby="page-content-title">
        <h2 id="page-content-title" class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-500">Kelola konten halaman</h2>

        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($pages as $key => $page)
                <a href="{{ route('admin.pages.edit', $key) }}" class="group flex items-center gap-3.5 rounded border border-gray-200 bg-white p-4 shadow-sm transition hover:border-primary/40">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded bg-primary-light text-primary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $pageIcons[$key] ?? $pageIcons['about'] }}"/></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-semibold text-gray-900 group-hover:text-primary">{{ $page['label'] }}</span>
                        <span class="block text-xs text-gray-500">{{ count($page['sections']) }} bagian dapat diubah</span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Pemisah visual antara "Kelola konten halaman" dan "Terakhir diubah" --}}
    <div class="relative mt-12 overflow-hidden rounded border border-primary/15 bg-primary-light px-5 py-4 sm:px-6" data-recent-banner>
        <div class="pointer-events-none absolute inset-0 opacity-[0.5]" aria-hidden="true" style="background-image: linear-gradient(to right, rgba(203, 30, 27, 0.07) 1px, transparent 1px), linear-gradient(to bottom, rgba(203, 30, 27, 0.07) 1px, transparent 1px); background-size: 32px 32px; mask-image: linear-gradient(to right, #000 15%, transparent 85%); -webkit-mask-image: linear-gradient(to right, #000 15%, transparent 85%);"></div>

        <div class="relative flex items-center gap-4">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded bg-white text-primary shadow-sm ring-1 ring-primary/10">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            </span>

            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900">Semua perubahan tercatat otomatis</p>
                <p class="mt-0.5 text-xs leading-5 text-gray-600">Siapa mengubah apa dan kapan dapat ditelusuri kembali di Riwayat Aktivitas.</p>
            </div>

            {{-- Ilustrasi garis waktu (hanya layar lebar) --}}
            <svg class="ml-auto hidden h-12 w-40 shrink-0 text-primary md:block" viewBox="0 0 160 48" fill="none" aria-hidden="true">
                <path d="M10 24h140" stroke="currentColor" stroke-opacity="0.25" stroke-width="2" stroke-linecap="round"/>
                <circle cx="22" cy="24" r="5" fill="currentColor" fill-opacity="0.35"/>
                <circle cx="62" cy="24" r="5" fill="currentColor" fill-opacity="0.55"/>
                <circle cx="102" cy="24" r="5" fill="currentColor" fill-opacity="0.8"/>
                <circle cx="142" cy="24" r="6" fill="currentColor"/>
                <rect x="12" y="6" width="20" height="6" rx="3" fill="currentColor" fill-opacity="0.18"/>
                <rect x="52" y="36" width="20" height="6" rx="3" fill="currentColor" fill-opacity="0.18"/>
                <rect x="92" y="6" width="20" height="6" rx="3" fill="currentColor" fill-opacity="0.18"/>
                <rect x="130" y="36" width="24" height="6" rx="3" fill="currentColor" fill-opacity="0.3"/>
            </svg>
        </div>
    </div>

    {{-- Terakhir diubah: lima perubahan isi situs terbaru (dari Riwayat Aktivitas) --}}
    <section class="mt-10" aria-labelledby="recent-title">
        <div class="mb-3 flex items-center justify-between gap-4">
            <h2 id="recent-title" class="text-xs font-bold uppercase tracking-wider text-gray-500">Terakhir diubah</h2>
            <a href="{{ route('admin.activity-log.index') }}" class="group inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:text-primary-dark">Lihat semua riwayat <x-admin.arrow class="transition group-hover:translate-x-1" /></a>
        </div>

        <div class="rounded border border-gray-200 bg-white shadow-sm">
            @forelse ($recent as $log)
                @php($time = $log->created_at->copy()->timezone(config('app.timezone'))->locale('id'))
                <div class="flex flex-col gap-2 border-b border-gray-100 px-5 py-3.5 last:border-b-0 sm:flex-row sm:items-center sm:gap-4">
                    <span class="inline-flex w-fit shrink-0 items-center gap-1.5 whitespace-nowrap rounded-sm px-2 py-0.5 text-xs font-medium sm:w-36 {{ $log->actionClass() }}">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $log->actionIcon() }}"/></svg>
                        {{ $log->actionLabel() }}
                    </span>

                    <div class="min-w-0 flex-1">
                        @php([$main, $note] = $log->detailLines())
                        <p class="text-sm text-gray-900">{{ $main }}</p>
                        @if ($note)
                            <p class="mt-0.5 text-xs text-gray-500">{{ $note }}</p>
                        @endif
                        <p class="mt-0.5 text-xs text-gray-500">
                            {{ $log->user_name }} &middot; {{ $log->menu }} &middot;
                            <time datetime="{{ $log->created_at->toIso8601String() }}" title="{{ $time->translatedFormat('d M Y, H:i') }}">{{ $time->diffForHumans() }}</time>
                        </p>
                    </div>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-gray-500">Belum ada perubahan konten. Perubahan yang Anda buat akan muncul di sini.</p>
            @endforelse
        </div>
    </section>
</x-app-layout>
