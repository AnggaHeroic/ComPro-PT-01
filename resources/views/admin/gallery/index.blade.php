<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Galeri</h1>
        <p class="mt-1 text-sm text-gray-500">Pilih aktivitas usaha untuk mengelola foto dokumentasinya.</p>
    </x-slot>

    <form method="GET" action="{{ route('admin.gallery.index') }}" class="mb-4 flex flex-wrap gap-2">
        <x-select name="category" class="w-auto" onchange="this.form.submit()">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
            @endforeach
        </x-select>
        <x-text-input name="q" type="search" :value="$search" placeholder="Cari nama, slug, atau KBLI..." class="max-w-xs" />
        <x-secondary-button type="submit">Cari</x-secondary-button>
        @if ($search !== '' || $categoryId)
            <a href="{{ route('admin.gallery.index') }}" class="inline-flex items-center px-2 text-sm text-gray-500 hover:text-gray-900">Reset</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-md border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Aktivitas</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">KBLI</th>
                        <th class="px-4 py-3 text-center">Foto</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($activities as $activity)
                        <tr class="hover:bg-gray-50">
                            <td class="max-w-sm px-4 py-3 font-medium text-gray-900">{{ $activity->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $activity->category->name }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $activity->kbli_code ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($activity->images_count > 0)
                                    <span class="rounded-sm bg-primary-light px-2 py-0.5 text-xs font-medium text-primary-dark">{{ $activity->images_count }} foto</span>
                                @else
                                    <span class="text-xs text-gray-400">Kosong</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.gallery.show', $activity) }}" class="font-medium text-gray-700 hover:text-primary">Kelola galeri</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-500">
                                @if ($search !== '' || $categoryId)
                                    Tidak ada aktivitas yang cocok dengan filter.
                                @else
                                    Belum ada aktivitas. <a href="{{ route('admin.activities.create') }}" class="font-medium text-primary hover:underline">Tambah aktivitas</a> terlebih dahulu.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $activities->links() }}
    </div>
</x-app-layout>
