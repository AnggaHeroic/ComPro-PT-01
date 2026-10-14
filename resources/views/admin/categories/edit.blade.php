<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Edit Kategori</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $category->name }}</p>
    </x-slot>

    <x-slot name="actions">
        <x-admin.back :href="route('admin.categories.index')" />
    </x-slot>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('admin.categories._form')
    </form>
</x-app-layout>
