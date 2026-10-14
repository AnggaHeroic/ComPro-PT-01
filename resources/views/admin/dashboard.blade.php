<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Selamat datang kembali, {{ auth()->user()->name }}.</p>
    </x-slot>

    <x-slot name="actions">
        <x-secondary-button size="sm" :href="route('home')" target="_blank" rel="noopener">Lihat website &nearr;</x-secondary-button>
    </x-slot>

    {{-- Banner: elemen visual yang sama dengan halaman login --}}
    <section class="relative isolate mb-8 overflow-hidden rounded bg-linear-to-br from-primary via-primary-dark to-[#6e0f0e] px-7 py-8 text-white sm:px-10 sm:py-10 lg:py-12">
        <div class="pointer-events-none absolute inset-0 -z-10 opacity-[0.12]" style="background-image: linear-gradient(to right, #fff 1px, transparent 1px), linear-gradient(to bottom, #fff 1px, transparent 1px); background-size: 40px 40px; background-size: 48px 48px; mask-image: radial-gradient(ellipse at 20% 20%, #000 10%, transparent 75%); -webkit-mask-image: radial-gradient(ellipse at 20% 20%, #000 10%, transparent 75%);"></div>

        {{-- Ilustrasi dekoratif: jendela dashboard (hanya layar lebar) --}}
        <div class="pointer-events-none absolute -right-4 top-1/2 hidden w-[25rem] -translate-y-1/2 lg:block xl:right-6" aria-hidden="true">
            <div class="overflow-hidden rounded border border-white/25 bg-white/10 shadow-2xl shadow-black/30 backdrop-blur-sm">
                {{-- Bar jendela --}}
                <div class="flex items-center gap-1.5 border-b border-white/15 px-3 py-2.5">
                    <span class="h-2 w-2 rounded-full bg-white/50"></span>
                    <span class="h-2 w-2 rounded-full bg-white/35"></span>
                    <span class="h-2 w-2 rounded-full bg-white/20"></span>
                    <span class="ms-3 h-2 w-28 rounded-full bg-white/15"></span>
                </div>

                <div class="flex">
                    {{-- Sidebar mini --}}
                    <div class="w-12 shrink-0 space-y-3 border-r border-white/15 px-3 py-4">
                        <span class="block h-2 rounded-full bg-white/70"></span>
                        <span class="block h-2 rounded-full bg-white/25"></span>
                        <span class="block h-2 rounded-full bg-white/25"></span>
                        <span class="block h-2 rounded-full bg-white/25"></span>
                    </div>

                    <div class="flex-1 space-y-3 p-4">
                        <span class="block h-2.5 w-1/3 rounded-full bg-white/55"></span>

                        {{-- Kartu statistik mini --}}
                        <div class="grid grid-cols-3 gap-2">
                            <div class="space-y-2 rounded-sm border border-white/15 bg-white/10 p-2.5">
                                <span class="block h-1.5 w-2/3 rounded-full bg-white/35"></span>
                                <span class="block h-3.5 w-1/2 rounded-sm bg-white/70"></span>
                            </div>
                            <div class="space-y-2 rounded-sm border border-white/15 bg-white/10 p-2.5">
                                <span class="block h-1.5 w-2/3 rounded-full bg-white/35"></span>
                                <span class="block h-3.5 w-2/3 rounded-sm bg-white/70"></span>
                            </div>
                            <div class="space-y-2 rounded-sm border border-white/15 bg-white/10 p-2.5">
                                <span class="block h-1.5 w-2/3 rounded-full bg-white/35"></span>
                                <span class="block h-3.5 w-1/3 rounded-sm bg-white/70"></span>
                            </div>
                        </div>

                        {{-- Grafik batang --}}
                        <div class="rounded-sm border border-white/15 bg-white/10 p-3">
                            <div class="flex h-16 items-end gap-2">
                                <span class="w-full rounded-t-sm bg-white/30" style="height: 40%"></span>
                                <span class="w-full rounded-t-sm bg-white/45" style="height: 65%"></span>
                                <span class="w-full rounded-t-sm bg-white/30" style="height: 50%"></span>
                                <span class="w-full rounded-t-sm bg-white/70" style="height: 90%"></span>
                                <span class="w-full rounded-t-sm bg-white/45" style="height: 70%"></span>
                                <span class="w-full rounded-t-sm bg-white/30" style="height: 55%"></span>
                                <span class="w-full rounded-t-sm bg-white/55" style="height: 80%"></span>
                            </div>
                        </div>

                        {{-- Baris daftar --}}
                        <div class="space-y-2">
                            <div class="flex items-center gap-2"><span class="h-4 w-6 rounded-sm bg-white/30"></span><span class="h-1.5 flex-1 rounded-full bg-white/25"></span><span class="h-4 w-8 rounded-sm bg-white/20"></span></div>
                            <div class="flex items-center gap-2"><span class="h-4 w-6 rounded-sm bg-white/30"></span><span class="h-1.5 w-2/3 rounded-full bg-white/25"></span><span class="ml-auto h-4 w-8 rounded-sm bg-white/20"></span></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kartu melayang: galeri --}}
            <div class="absolute -bottom-5 -left-8 w-32 rounded border border-white/25 bg-primary-dark/70 p-2.5 backdrop-blur-md">
                <div class="grid grid-cols-2 gap-1.5">
                    <span class="flex h-8 items-center justify-center rounded-sm bg-white/25 text-white/80">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="1.5"/><path d="M21 16l-5-5-8 9"/></svg>
                    </span>
                    <span class="flex h-8 items-center justify-center rounded-sm bg-white/10 text-white/60">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="1.5"/><path d="M21 16l-5-5-8 9"/></svg>
                    </span>
                </div>
                <span class="mt-2 block h-1.5 w-2/3 rounded-full bg-white/40"></span>
            </div>
        </div>

        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/70">Panel Admin</p>
        <h2 class="mt-2 max-w-xl font-display lg:max-w-md text-2xl font-bold leading-tight sm:text-3xl">Kelola konten website dengan mudah.</h2>
        <p class="mt-3 max-w-xl text-sm lg:max-w-md leading-relaxed text-white/75">Perbarui kategori layanan, aktivitas usaha, dan galeri tanpa menyentuh kode.</p>

        <div class="mt-6 flex flex-wrap gap-3 lg:max-w-md">
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
