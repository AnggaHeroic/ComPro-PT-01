<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Tambah Aktivitas</h1>
        <p class="mt-1 text-sm text-gray-500">Aktivitas usaha ditampilkan pada halaman kategori dan halaman detail.</p>
    </x-slot>

    <x-slot name="actions">
        <x-admin.back :href="route('admin.activities.index', array_filter(['category' => $activity->service_category_id]))" />
    </x-slot>

    <form method="POST" action="{{ route('admin.activities.store') }}" enctype="multipart/form-data" data-unsaved-guard {{ collect($errors->getBags())->contains(fn ($bag) => $bag->any()) ? 'data-unsaved-dirty' : '' }}>
        @include('admin.activities._form')
    </form>
</x-app-layout>
