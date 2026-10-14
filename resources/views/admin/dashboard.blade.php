<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Selamat datang kembali, {{ auth()->user()->name }}.</p>
    </x-slot>

    <x-slot name="actions">
        <x-secondary-button size="sm" :href="route('home')" target="_blank" rel="noopener">Lihat website &nearr;</x-secondary-button>
    </x-slot>

    {{-- Banner: elemen visual yang sama dengan halaman login --}}
    <section class="relative isolate mb-8 overflow-hidden rounded bg-linear-to-br from-primary via-primary-dark to-[#6e0f0e] px-7 py-8 text-white sm:px-10 sm:py-10">
        <div class="pointer-events-none absolute inset-0 -z-10 opacity-[0.12]" style="background-image: linear-gradient(to right, #fff 1px, transparent 1px), linear-gradient(to bottom, #fff 1px, transparent 1px); background-size: 40px 40px; background-size: 48px 48px; mask-image: radial-gradient(ellipse at 20% 20%, #000 10%, transparent 75%); -webkit-mask-image: radial-gradient(ellipse at 20% 20%, #000 10%, transparent 75%);"></div>
        <svg class="pointer-events-none absolute -bottom-16 -right-10 -z-10 h-72 w-72 text-white/[0.08]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9 21v-6h6v6"/></svg>

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
