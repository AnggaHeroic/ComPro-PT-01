<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Edit Aktivitas</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $activity->name }}</p>
    </x-slot>

    <x-slot name="actions">
        <x-secondary-button size="sm" :href="route('admin.gallery.show', $activity)">Kelola galeri</x-secondary-button>
        <x-admin.back :href="route('admin.activities.index', ['category' => $activity->service_category_id])" />
    </x-slot>

    <form method="POST" action="{{ route('admin.activities.update', $activity) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('admin.activities._form')
    </form>
</x-app-layout>
