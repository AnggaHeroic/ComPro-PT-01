<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-gray-900">Kategori</h1>
                <p class="mt-1 text-sm text-gray-500">Kelola kategori layanan yang tampil di website.</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
                + Tambah Kategori
            </a>
        </div>
    </x-slot>

    <form method="GET" action="{{ route('admin.categories.index') }}" class="mb-4 flex gap-2">
        <x-text-input name="q" type="search" :value="$search" placeholder="Cari nama atau slug..." class="max-w-xs" />
        <x-secondary-button type="submit">Cari</x-secondary-button>
        @if ($search !== '')
            <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center px-2 text-sm text-gray-500 hover:text-gray-900">Reset</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-md border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Urutan</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3 text-center">Aktivitas</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $category->sort_order }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-14 shrink-0 items-center justify-center overflow-hidden rounded-sm bg-gray-100">
                                        @if ($category->image_url)
                                            <img src="{{ $category->image_url }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                        @endif
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $category->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $category->slug }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.activities.index', ['category' => $category->id]) }}" class="font-medium text-primary hover:underline">{{ $category->activities_count }}</a>
                            </td>
                            <td class="px-4 py-3">
                                @if ($category->is_published)
                                    <span class="rounded-sm bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">Terbit</span>
                                @else
                                    <span class="rounded-sm bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="font-medium text-gray-700 hover:text-primary">Edit</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori &quot;{{ e($category->name) }}&quot;? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-gray-500 hover:text-primary">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                                @if ($search !== '')
                                    Tidak ada kategori yang cocok dengan "{{ $search }}".
                                @else
                                    Belum ada kategori. <a href="{{ route('admin.categories.create') }}" class="font-medium text-primary hover:underline">Tambah kategori pertama</a>.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $categories->links() }}
    </div>
</x-app-layout>
