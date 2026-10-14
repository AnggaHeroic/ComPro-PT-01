<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Kategori</h1>
        <p class="mt-1 text-sm text-gray-500">Kelola kategori layanan yang tampil di website.</p>
    </x-slot>

    @php
        $filtered = $search !== '' || $status;
        $total = $reorder ? $categories->count() : $categories->total();
    @endphp

    <div
        class="overflow-hidden rounded border border-gray-200 bg-white shadow-sm"
        @if ($reorder) x-data="reorderList({ url: '{{ route('admin.categories.reorder') }}' })" @endif
    >
        @if ($reorder)
            {{-- Mode atur urutan --}}
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 border-b border-gray-200 bg-primary-light/60 px-4 py-3">
                <x-admin.reorder-toggle :active="true" :href="route('admin.categories.index')" />

                <p class="text-sm text-gray-600">Seret baris atau pakai tombol panah untuk mengubah urutan. Perubahan langsung tersimpan.</p>

                <x-admin.reorder-status />

                <x-secondary-button :href="route('admin.categories.index')" class="ml-auto">Selesai</x-secondary-button>
            </div>
        @else
            <x-admin.status-tabs :current="$status" :counts="$counts" route="admin.categories.index" :params="request()->only(['q'])" />

            <form method="GET" action="{{ route('admin.categories.index') }}" class="flex flex-wrap items-center gap-2 border-b border-gray-200 bg-gray-50 px-4 py-3">
                @if ($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif

                <x-admin.search :value="$search" placeholder="Cari nama atau alamat URL..." />

                <x-secondary-button type="submit">Cari</x-secondary-button>

                @if ($filtered)
                    <a href="{{ route('admin.categories.index') }}" class="px-2 text-sm text-gray-500 hover:text-primary">Reset</a>
                @endif

                <span class="px-1 text-sm text-gray-500">{{ $total }} kategori</span>

                <div class="ml-auto flex items-center gap-4">
                    <x-admin.reorder-toggle
                        :href="route('admin.categories.index', ['reorder' => 1])"
                        :disabled="$filtered || $total < 2"
                        :reason="$filtered ? 'Hapus pencarian atau filter untuk mengatur urutan.' : 'Perlu minimal dua kategori.'"
                    />

                    <x-primary-button :href="route('admin.categories.create')">+ Tambah Kategori</x-primary-button>
                </div>
            </form>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-secondary-dark text-center text-xs font-bold uppercase tracking-wider text-white [&>tr>th]:border-b-2 [&>tr>th]:border-primary">
                    <tr>
                        @if ($reorder)
                            <th class="px-4 py-3">Pindah</th>
                        @endif
                        <th class="px-4 py-3">Urutan</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Aktivitas</th>
                        <th class="px-4 py-3">Status</th>
                        @unless ($reorder)
                            <th class="px-4 py-3">Aksi</th>
                        @endunless
                    </tr>
                </thead>
                <tbody
                    @class(['divide-y divide-gray-100', 'select-none' => $reorder])
                    @if ($reorder) x-ref="body" @pointerdown="down($event)" @pointermove="drag($event)" @pointerup="drop($event)" @pointercancel="drop($event)" @endif
                >
                    @forelse ($categories as $category)
                        <tr
                            @class(['transition', 'hover:bg-gray-50' => ! $reorder, 'bg-white hover:bg-primary-light/40' => $reorder])
                            @if ($reorder)
                                data-id="{{ $category->id }}"
                                data-group=""
                            @endif
                        >
                            @if ($reorder)
                                <td class="px-4 py-3 text-center">
                                    <x-admin.reorder-handle :label="$category->name" />
                                </td>
                            @endif
                            <td class="px-4 py-3 text-center text-gray-500" data-position>{{ $category->sort_order }}</td>
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
                            @unless ($reorder)
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
                            @endunless
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-14 text-center text-gray-500">
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

        @if (! $reorder && $categories->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-4 py-3">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
