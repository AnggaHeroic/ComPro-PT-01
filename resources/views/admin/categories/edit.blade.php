<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Edit Kategori</h1>
        <div class="mt-0.5 flex min-w-0 flex-col gap-0.5 lg:flex-row lg:items-center lg:gap-3">
            <p class="mt-0! min-w-0 text-sm text-gray-500">{{ $category->name }}</p>
            <x-admin.last-changed :subject="$category" :since="$category->updated_at" :created="$category->created_at" class="lg:border-l lg:border-gray-300 lg:pl-3" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-admin.back :href="route('admin.categories.index')" />
    </x-slot>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" data-unsaved-guard {{ collect($errors->getBags())->contains(fn ($bag) => $bag->any()) ? 'data-unsaved-dirty' : '' }}>
        @method('PUT')
        @include('admin.categories._form')
    </form>
</x-app-layout>
