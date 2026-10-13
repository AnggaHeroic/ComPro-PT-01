<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Edit Aktivitas</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $activity->name }}</p>
    </x-slot>

    <x-admin.card class="max-w-3xl">
        <form method="POST" action="{{ route('admin.activities.update', $activity) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.activities._form')
        </form>
    </x-admin.card>
</x-app-layout>
