<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Aktivitas Usaha</h1>
        <p class="mt-1 text-sm text-gray-500">Kelola aktivitas usaha dan kode KBLI di setiap kategori.</p>
    </x-slot>

    @php
        $filtered = $search !== '' || $status;
        $total = $reorder ? $activities->count() : $activities->total();
        $categoryParam = array_filter(['category' => $categoryId]);
        $positions = [];
        $previousCategory = null;
    @endphp

    <div
        class="overflow-hidden rounded border border-gray-200 bg-white shadow-sm"
        @if ($reorder) x-data="reorderList({ url: '{{ route('admin.activities.reorder') }}' })" @endif
    >
        @if ($reorder)
            {{-- Mode atur urutan: urutan berlaku di dalam kategori masing-masing --}}
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 border-b border-gray-200 bg-primary-light/60 px-4 py-3">
                <x-admin.reorder-toggle :active="true" :href="route('admin.activities.index', $categoryParam)" />

                <p class="text-sm text-gray-600">Seret baris atau pakai tombol panah untuk mengubah urutan di dalam kategorinya. Perubahan langsung tersimpan.</p>

                <x-admin.reorder-status />

                <x-secondary-button :href="route('admin.activities.index', $categoryParam)" class="ml-auto">Selesai</x-secondary-button>
            </div>
        @else
            <x-admin.status-tabs :current="$status" :counts="$counts" route="admin.activities.index" :params="request()->only(['q', 'category'])" />

            <form method="GET" action="{{ route('admin.activities.index') }}" class="flex flex-wrap items-center gap-2 border-b border-gray-200 bg-gray-50 px-4 py-3">
                @if ($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif

                <x-select name="category" class="w-full sm:w-56" onchange="this.form.submit()" aria-label="Filter kategori">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </x-select>

                <x-admin.search :value="$search" placeholder="Cari nama, alamat URL, atau KBLI..." />

                <x-secondary-button type="submit">Cari</x-secondary-button>

                @if ($filtered || $categoryId)
                    <a href="{{ route('admin.activities.index') }}" class="px-2 text-sm text-gray-500 hover:text-primary">Reset</a>
                @endif

                <span class="px-1 text-sm text-gray-500">{{ $total }} aktivitas</span>

                <div class="ml-auto flex items-center gap-4">
                    <x-admin.reorder-toggle
                        :href="route('admin.activities.index', $categoryParam + ['reorder' => 1])"
                        :disabled="$filtered || $total < 2"
                        :reason="$filtered ? 'Hapus pencarian atau filter status untuk mengatur urutan.' : 'Perlu minimal dua aktivitas.'"
                    />

                    <x-primary-button :href="route('admin.activities.create', $categoryParam)">+ Tambah Aktivitas</x-primary-button>
                </div>
            </form>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-secondary-dark text-center text-xs font-bold uppercase tracking-wider text-white [&>tr>th]:border-b-2 [&>tr>th]:border-primary">
                    <tr>
                        @if ($reorder)
                            <th class="px-4 py-3">Pindah</th>
                            <th class="px-4 py-3">No.</th>
                        @endif
                        <th class="px-4 py-3">Aktivitas</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Galeri</th>
                        <th class="px-4 py-3">Status</th>
                        @unless ($reorder)
                            <th class="px-4 py-3">Aksi</th>
                        @endunless
                    </tr>
                </thead>
                <tbody
                    class="divide-y divide-gray-100"
                    @if ($reorder) x-ref="body" @dragover="over($event)" @drop.prevent @endif
                >
                    @forelse ($activities as $activity)
                        @php
                            $positions[$activity->service_category_id] = ($positions[$activity->service_category_id] ?? 0) + 1;
                            $startsGroup = $reorder && $previousCategory !== null && $previousCategory !== $activity->service_category_id;
                            $previousCategory = $activity->service_category_id;
                        @endphp

                        <tr
                            @class([
                                'transition',
                                'hover:bg-gray-50' => ! $reorder,
                                'cursor-grab bg-white hover:bg-primary-light/40' => $reorder,
                                'border-t-4 border-gray-200' => $startsGroup,
                            ])
                            @if ($reorder)
                                data-id="{{ $activity->id }}"
                                data-group="{{ $activity->service_category_id }}"
                                draggable="true"
                                @dragstart="start($event)"
                                @dragend="end()"
                            @endif
                        >
                            @if ($reorder)
                                <td class="px-4 py-3 text-center">
                                    <x-admin.reorder-handle :label="$activity->name" />
                                </td>
                                <td class="px-4 py-3 text-center text-gray-500" data-position>{{ $positions[$activity->service_category_id] }}</td>
                            @endif
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-11 w-16 shrink-0 items-center justify-center overflow-hidden rounded-sm bg-gray-100">
                                        @if ($activity->image_url)
                                            <img src="{{ $activity->image_url }}" alt="" class="h-full w-full object-cover" loading="lazy" draggable="false">
                                        @endif
                                    </span>
                                    <div class="min-w-0">
                                        <p class="max-w-md font-medium leading-snug text-gray-900">{{ $activity->name }}</p>
                                        <p class="mt-0.5 text-xs text-gray-500">
                                            @if ($activity->kbli_code)
                                                <span class="font-mono">KBLI {{ $activity->kbli_code }}</span> &middot;
                                            @endif
                                            @if ($activity->slug)<span class="font-mono">{{ $activity->slug }}</span>@else<span class="italic">Belum ada URL (draft)</span>@endif
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block whitespace-nowrap rounded-sm bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ $activity->category->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.gallery.show', $activity) }}" class="inline-flex items-center gap-1 text-xs font-semibold {{ $activity->images_count > 0 ? 'text-primary-dark' : 'text-gray-400' }} hover:text-primary" title="Kelola galeri" @if ($reorder) draggable="false" @endif>
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="1.5"/><path d="M21 16l-5-5-8 9"/></svg>
                                    {{ $activity->images_count }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($activity->is_published)
                                    <span class="rounded-sm bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Terbit</span>
                                @else
                                    <span class="rounded-sm bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">Draft</span>
                                @endif
                            </td>
                            @unless ($reorder)
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.activities.edit', $activity) }}" class="whitespace-nowrap rounded bg-secondary-dark px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-primary">Edit</a>
                                        <form method="POST" action="{{ route('admin.activities.destroy', $activity) }}" onsubmit="return confirm('Hapus aktivitas &quot;{{ e($activity->name) }}&quot; beserta galerinya? Tindakan ini tidak dapat dibatalkan.')">
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
                            <td colspan="7" class="px-4 py-14 text-center text-gray-500">
                                @if ($search !== '' || $categoryId || $status)
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

        @if (! $reorder && $activities->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-4 py-3">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
