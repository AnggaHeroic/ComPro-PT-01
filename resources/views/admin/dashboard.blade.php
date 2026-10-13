<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Selamat datang kembali, {{ auth()->user()->name }}.</p>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2">
        <a href="{{ route('home') }}" target="_blank" rel="noopener"
           class="group flex items-start gap-4 rounded-md border border-gray-200 bg-white p-5 transition hover:border-primary/50">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-primary-light text-primary">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6"/><path d="M20 4l-9 9"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>
            </span>
            <span>
                <span class="block font-semibold text-gray-900 group-hover:text-primary">Lihat Website</span>
                <span class="mt-0.5 block text-sm text-gray-500">Buka tampilan publik di tab baru.</span>
            </span>
        </a>

        <a href="{{ route('admin.profile.edit') }}"
           class="group flex items-start gap-4 rounded-md border border-gray-200 bg-white p-5 transition hover:border-primary/50">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-primary-light text-primary">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
            </span>
            <span>
                <span class="block font-semibold text-gray-900 group-hover:text-primary">Profil Akun</span>
                <span class="mt-0.5 block text-sm text-gray-500">Ubah nama, email, dan password.</span>
            </span>
        </a>
    </div>

    <x-admin.card class="mt-6 border-dashed bg-transparent" title="Pengelolaan Konten" description="Menu kategori, aktivitas, galeri, dan pengaturan akan muncul di sidebar pada fase berikutnya." />
</x-app-layout>
