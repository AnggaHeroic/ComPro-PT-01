<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Kategori</h1>
        <p class="mt-1 text-sm text-gray-500">Kelola kategori layanan yang tampil di website.</p>
    </x-slot>

    <div class="overflow-hidden rounded border border-gray-200 bg-white shadow-sm">
    <x-admin.status-tabs :current="$status" :counts="$counts" route="admin.categories.index" :params="request()->only(['q'])" />

    <form method="GET" action="{{ route('admin.categories.index') }}" class="flex flex-wrap items-center gap-2 border-b border-gray-200 bg-gray-50 px-4 py-3">
        @if ($status)
            <input type="hidden" name="status" value="{{ $status }}">
        @endif

        <x-admin.search :value="$search" placeholder="Cari nama atau alamat URL..." />

        <x-secondary-button type="submit">Cari</x-secondary-button>

        @if ($search !== '' || $status)
            <a href="{{ route('admin.categories.index') }}" class="px-2 text-sm text-gray-500 hover:text-primary">Reset</a>
        @endif

        <span class="px-1 text-sm text-gray-500">{{ $categories->total() }} kategori</span>

        <x-primary-button :href="route('admin.categories.create')" class="ml-auto">+ Tambah Kategori</x-primary-button>
    </form>

    <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-secondary-dark text-center text-xs font-bold uppercase tracking-wider text-white [&>tr>th]:border-b-2 [&>tr>th]:border-primary">
                    <tr>
                        <th class="px-4 py-3">Urutan</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Aktivitas</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($categories as $category)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-4 py-3 text-center text-gray-500">{{ $category->sort_order }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-11 w-16 shrink-0 items-center justify-center overflow-hidden rounded-sm bg-gray-100">
                                        @if ($category->image_url)
                                            <img src="{{ $category->image_url }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                        @endif
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-medium leading-snug text-gray-900">{{ $category->name }}</p>
                                        <p class="mt-0.5 text-xs text-gray-500">@if ($category->slug)<span class="font-mono">{{ $category->slug }}</span>@else<span class="italic">Belum ada URL (draft)</span>@endif</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.activities.index', ['category' => $category->id]) }}" class="inline-flex min-w-7 justify-center rounded-sm bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700 transition hover:bg-primary-light hover:text-primary-dark" title="Lihat aktivitas">{{ $category->activities_count }}</a>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($category->is_published)
                                    <span class="rounded-sm bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Terbit</span>
                                @else
                                    <span class="rounded-sm bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="whitespace-nowrap rounded bg-secondary-dark px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-primary">Edit</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori &quot;{{ e($category->name) }}&quot;? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded border border-primary/30 bg-primary-light px-3 py-1.5 text-xs font-semibold text-primary-dark transition hover:bg-primary hover:text-white">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-14 text-center text-gray-500">
                                @if ($search !== '')
                                    Tidak ada kategori yang cocok dengan "{{ $search }}".
                                @elseif ($status)
                                    Tidak ada kategori {{ $status === 'draft' ? 'draft' : 'terbit' }}.
                                @else
                                    Belum ada kategori. <a href="{{ route('admin.categories.create') }}" class="font-medium text-primary hover:underline">Tambah kategori pertama</a>.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-4 py-3">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
