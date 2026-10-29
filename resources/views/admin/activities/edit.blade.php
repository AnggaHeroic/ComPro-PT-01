<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Edit Aktivitas</h1>
        <div class="mt-0.5 flex min-w-0 flex-col gap-0.5 lg:flex-row lg:items-center lg:gap-3">
            <p class="mt-0! min-w-0 text-sm text-gray-500">{{ $activity->name }}</p>
            <x-admin.last-changed :subject="$activity" :since="$activity->updated_at" :created="$activity->created_at" class="lg:border-l lg:border-gray-300 lg:pl-3" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-secondary-button size="sm" :href="route('admin.gallery.show', $activity)">Kelola galeri</x-secondary-button>
        <x-admin.back :href="route('admin.activities.index', ['category' => $activity->service_category_id])" />
    </x-slot>

    <form method="POST" action="{{ route('admin.activities.update', $activity) }}" enctype="multipart/form-data" data-unsaved-guard {{ collect($errors->getBags())->contains(fn ($bag) => $bag->any()) ? 'data-unsaved-dirty' : '' }}>
        @method('PUT')
        @include('admin.activities._form')
    </form>
</x-app-layout>
