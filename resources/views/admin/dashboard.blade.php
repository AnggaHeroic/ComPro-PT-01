<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Selamat datang kembali, {{ auth()->user()->name }}.</p>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2">
        <a href="{{ route('admin.categories.index') }}" class="group rounded-md border border-gray-200 bg-white p-5 transition hover:border-primary/50">
            <p class="text-sm font-medium text-gray-500 group-hover:text-primary">Kategori</p>
            <p class="mt-2 font-display text-3xl font-bold text-gray-900">{{ $categoryCount }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $categoryPublished }} terbit, {{ $categoryCount - $categoryPublished }} draft</p>
        </a>

        <a href="{{ route('admin.activities.index') }}" class="group rounded-md border border-gray-200 bg-white p-5 transition hover:border-primary/50">
            <p class="text-sm font-medium text-gray-500 group-hover:text-primary">Aktivitas Usaha</p>
            <p class="mt-2 font-display text-3xl font-bold text-gray-900">{{ $activityCount }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $activityPublished }} terbit, {{ $activityCount - $activityPublished }} draft</p>
        </a>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('admin.categories.create') }}" class="rounded-md border border-gray-200 bg-white px-5 py-4 text-sm font-medium text-gray-700 transition hover:border-primary/50 hover:text-primary">+ Tambah kategori</a>
        <a href="{{ route('admin.activities.create') }}" class="rounded-md border border-gray-200 bg-white px-5 py-4 text-sm font-medium text-gray-700 transition hover:border-primary/50 hover:text-primary">+ Tambah aktivitas</a>
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="rounded-md border border-gray-200 bg-white px-5 py-4 text-sm font-medium text-gray-700 transition hover:border-primary/50 hover:text-primary">Lihat website &nearr;</a>
    </div>
</x-app-layout>
