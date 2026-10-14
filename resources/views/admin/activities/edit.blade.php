<x-app-layout>
    <x-slot name="header">
        <x-admin.back :href="route('admin.activities.index', ['category' => $activity->service_category_id])" label="Kembali ke Aktivitas Usaha" />
        <h1 class="font-display text-2xl font-bold text-gray-900">Edit Aktivitas</h1>
        <p class="mt-1 text-sm text-gray-500">
            {{ $activity->name }}
            &middot; <a href="{{ route('admin.gallery.show', $activity) }}" class="font-medium text-primary hover:underline">Kelola galeri</a>
        </p>
    </x-slot>

    <x-admin.card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.activities.update', $activity) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.activities._form')
        </form>
    </x-admin.card>
</x-app-layout>
