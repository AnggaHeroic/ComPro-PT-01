<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Selamat datang kembali, {{ auth()->user()->name }}.</p>
    </x-slot>

    <x-slot name="actions">
        <x-secondary-button size="sm" :href="route('home')" target="_blank" rel="noopener">Lihat website &nearr;</x-secondary-button>
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

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center rounded bg-white px-4 py-2.5 text-sm font-semibold text-primary-dark transition hover:bg-primary-light">+ Tambah kategori</a>
            <a href="{{ route('admin.activities.create') }}" class="inline-flex items-center rounded bg-white px-4 py-2.5 text-sm font-semibold text-primary-dark transition hover:bg-primary-light">+ Tambah aktivitas</a>
            <a href="{{ route('admin.gallery.index') }}" class="inline-flex items-center rounded border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">Kelola galeri</a>
            <a href="{{ route('admin.profile.edit') }}" class="inline-flex items-center rounded border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">Profil akun</a>
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
            <p class="mt-4 text-sm font-semibold text-primary">Kelola kategori <span class="inline-block transition group-hover:translate-x-1">&rarr;</span></p>
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
            <p class="mt-4 text-sm font-semibold text-primary">Kelola aktivitas <span class="inline-block transition group-hover:translate-x-1">&rarr;</span></p>
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
            <p class="mt-4 text-sm font-semibold text-primary">Kelola galeri <span class="inline-block transition group-hover:translate-x-1">&rarr;</span></p>
        </a>
    </div>

</x-app-layout>
