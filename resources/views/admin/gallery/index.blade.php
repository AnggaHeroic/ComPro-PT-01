<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Galeri</h1>
        <p class="mt-1 text-sm text-gray-500">Pilih aktivitas usaha untuk mengelola foto dokumentasinya.</p>
    </x-slot>

    <div class="overflow-hidden rounded border border-gray-200 bg-white shadow-sm">
    <form method="GET" action="{{ route('admin.gallery.index') }}" class="flex flex-wrap items-center gap-2 border-b border-gray-200 bg-gray-50 px-4 py-3">
        <x-select name="category" class="w-full sm:w-56" onchange="this.form.submit()" aria-label="Filter kategori">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
            @endforeach
        </x-select>

        <x-admin.search :value="$search" placeholder="Cari nama, alamat URL, atau KBLI..." />

        <x-secondary-button type="submit">Cari</x-secondary-button>

                {{-- Jumlah data: baris sendiri di bawah pencarian pada mobile --}}
                <div class="flex w-full items-center gap-2 sm:w-auto">
            @if ($search !== '' || $categoryId)
                <a href="{{ route('admin.gallery.index') }}" class="px-2 text-sm text-gray-500 hover:text-primary">Reset</a>
            @endif
            <span class="px-1 text-sm text-gray-500">{{ $activities->total() }} aktivitas</span>
                </div>
    </form>

    <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-secondary-dark text-center text-xs font-bold uppercase tracking-wider text-white [&>tr>th]:border-b-2 [&>tr>th]:border-primary">
                    <tr>
                        <th class="px-4 py-3">Aktivitas</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Foto</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($activities as $activity)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-11 w-16 shrink-0 items-center justify-center overflow-hidden rounded-sm bg-gray-100">
                                        @if ($activity->image_url)
                                            <img src="{{ $activity->image_url }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                        @endif
                                    </span>
                                    <div class="min-w-0">
                                        <p class="max-w-md font-medium leading-snug text-gray-900">{{ $activity->name }}</p>
                                        @if ($activity->kbli_code)
                                            <p class="mt-0.5 font-mono text-xs text-gray-500">KBLI {{ $activity->kbli_code }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block whitespace-nowrap rounded-sm bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ $activity->category->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($activity->images_count > 0)
                                    <span class="inline-flex items-center gap-1 rounded-sm bg-primary-light px-2 py-0.5 text-xs font-semibold text-primary-dark">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="1.5"/><path d="M21 16l-5-5-8 9"/></svg>
                                        {{ $activity->images_count }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Kosong</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.gallery.show', $activity) }}" class="inline-flex items-center gap-1.5 whitespace-nowrap rounded bg-secondary-dark px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-primary">
                                    Kelola galeri
                                    <span aria-hidden="true">&rarr;</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-14 text-center text-gray-500">
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

        @if ($activities->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-4 py-3">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
