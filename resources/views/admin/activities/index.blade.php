<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-gray-900">Aktivitas Usaha</h1>
                <p class="mt-1 text-sm text-gray-500">Kelola aktivitas usaha dan kode KBLI di setiap kategori.</p>
            </div>
            <a href="{{ route('admin.activities.create', array_filter(['category' => $categoryId])) }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
                + Tambah Aktivitas
            </a>
        </div>
    </x-slot>

    <form method="GET" action="{{ route('admin.activities.index') }}" class="mb-4 flex flex-wrap gap-2">
        <x-select name="category" class="w-auto" onchange="this.form.submit()">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
            @endforeach
        </x-select>
        <x-text-input name="q" type="search" :value="$search" placeholder="Cari nama, slug, atau KBLI..." class="max-w-xs" />
        <x-secondary-button type="submit">Cari</x-secondary-button>
        @if ($search !== '' || $categoryId)
            <a href="{{ route('admin.activities.index') }}" class="inline-flex items-center px-2 text-sm text-gray-500 hover:text-gray-900">Reset</a>
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
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3 text-center">Galeri</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($activities as $activity)
                        <tr class="hover:bg-gray-50">
                            <td class="max-w-xs px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-14 shrink-0 items-center justify-center overflow-hidden rounded-sm bg-gray-100">
                                        @if ($activity->image_url)
                                            <img src="{{ $activity->image_url }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                        @endif
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $activity->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $activity->category->name }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $activity->kbli_code ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $activity->slug }}</td>
                            <td class="px-4 py-3 text-center"><a href="{{ route('admin.gallery.show', $activity) }}" class="font-medium text-primary hover:underline" title="Kelola galeri">{{ $activity->images_count }}</a></td>
                            <td class="px-4 py-3">
                                @if ($activity->is_published)
                                    <span class="rounded-sm bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">Terbit</span>
                                @else
                                    <span class="rounded-sm bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.activities.edit', $activity) }}" class="font-medium text-gray-700 hover:text-primary">Edit</a>
                                    <form method="POST" action="{{ route('admin.activities.destroy', $activity) }}" onsubmit="return confirm('Hapus aktivitas &quot;{{ e($activity->name) }}&quot; beserta galerinya? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-gray-500 hover:text-primary">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                @if ($search !== '' || $categoryId)
                                    Tidak ada aktivitas yang cocok dengan filter.
                                @else
                                    Belum ada aktivitas. <a href="{{ route('admin.activities.create') }}" class="font-medium text-primary hover:underline">Tambah aktivitas pertama</a>.
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
