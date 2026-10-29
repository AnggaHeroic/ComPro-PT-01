<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Tambah Kategori</h1>
        <p class="mt-1 text-sm text-gray-500">Kategori mengelompokkan aktivitas usaha di halaman Layanan &amp; Jasa.</p>
    </x-slot>

    <x-slot name="actions">
        <x-admin.back :href="route('admin.categories.index')" />
    </x-slot>

    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" data-unsaved-guard {{ collect($errors->getBags())->contains(fn ($bag) => $bag->any()) ? 'data-unsaved-dirty' : '' }}>
        @include('admin.categories._form')
    </form>
</x-app-layout>
