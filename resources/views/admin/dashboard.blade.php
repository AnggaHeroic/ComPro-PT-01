<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Selamat datang kembali, {{ auth()->user()->name }}.</p>
    </x-slot>

    <x-slot name="actions">
        <x-secondary-button size="sm" :href="route('home')" target="_blank" rel="noopener">Lihat website &nearr;</x-secondary-button>
    </x-slot>

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

    {{-- Aksi cepat --}}
    <x-admin.card title="Aksi cepat" description="Pintasan untuk pekerjaan yang paling sering dilakukan." class="mt-8">
        <div class="flex flex-wrap gap-3">
            <x-primary-button :href="route('admin.categories.create')">+ Tambah kategori</x-primary-button>
            <x-primary-button :href="route('admin.activities.create')">+ Tambah aktivitas</x-primary-button>
            <x-secondary-button :href="route('admin.gallery.index')">Kelola galeri</x-secondary-button>
            <x-secondary-button :href="route('admin.profile.edit')">Profil akun</x-secondary-button>
        </div>
    </x-admin.card>
</x-app-layout>
