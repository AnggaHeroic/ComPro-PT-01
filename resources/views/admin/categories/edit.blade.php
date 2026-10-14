<x-app-layout>
    <x-slot name="header">
        <x-admin.back :href="route('admin.categories.index')" label="Kembali ke Kategori" />
        <h1 class="font-display text-2xl font-bold text-gray-900">Edit Kategori</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $category->name }}</p>
    </x-slot>

    <x-admin.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.categories._form')
        </form>
    </x-admin.card>
</x-app-layout>
