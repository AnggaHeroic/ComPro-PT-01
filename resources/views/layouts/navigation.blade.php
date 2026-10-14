@php
    $user = Auth::user();
    $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1));
@endphp

{{-- Mobile backdrop --}}
<div
    x-show="sidebar"
    x-cloak
    x-transition.opacity
    @click="sidebar = false"
    class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
    aria-hidden="true"
></div>

<aside
    :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-gray-200 bg-white transition-transform duration-200 ease-out"
    aria-label="Navigasi admin"
>
    {{-- Brand --}}
    <div class="flex h-16 shrink-0 items-center justify-between border-b border-gray-200 px-5">
        <a href="{{ route('admin.dashboard') }}" aria-label="Dashboard">
            <x-application-logo class="h-9 w-auto mix-blend-multiply" />
        </a>
        <button type="button" @click="sidebar = false" aria-label="Tutup menu" class="rounded-md p-1.5 text-gray-500 hover:bg-gray-100 lg:hidden">
            <x-icons.close class="h-5 w-5" />
        </button>
    </div>

    {{-- Menu --}}
    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
        <div>
            <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Menu</p>
            <div class="space-y-1">
                <x-side-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
                    Dashboard
                </x-side-link>
            </div>
        </div>

        <div>
            <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Konten</p>
            <div class="space-y-1">
                <x-side-link :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 12l9 4 9-4"/><path d="M3 17l9 4 9-4"/></svg>
                    Kategori
                </x-side-link>
                <x-side-link :href="route('admin.activities.index')" :active="request()->routeIs('admin.activities.*')">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/></svg>
                    Aktivitas Usaha
                </x-side-link>
                <x-side-link :href="route('admin.gallery.index')" :active="request()->routeIs('admin.gallery.*')">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="1.5"/><path d="M21 16l-5-5-8 9"/></svg>
                    Galeri
                </x-side-link>
            </div>
        </div>

        <div>
            <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Akun</p>
            <div class="space-y-1">
                <x-side-link :href="route('admin.profile.edit')" :active="request()->routeIs('admin.profile.*')">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
                    Profil
                </x-side-link>
                <x-side-link :href="route('home')" target="_blank" rel="noopener">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6"/><path d="M20 4l-9 9"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>
                    Lihat Website
                </x-side-link>
            </div>
        </div>
    </nav>

    {{-- User --}}
    <div class="shrink-0 border-t border-gray-200 p-3">
        <div class="flex items-center gap-3 px-2 py-2">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-white">{{ $initial }}</span>
            <div class="min-w-0">
                <p class="truncate text-sm font-medium text-gray-900">{{ $user->name }}</p>
                <p class="truncate text-xs text-gray-500">{{ $user->email }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-1">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-primary focus:outline-none">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                Keluar
            </button>
        </form>
    </div>
</aside>
