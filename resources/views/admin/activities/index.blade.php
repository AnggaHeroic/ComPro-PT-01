<x-app-layout>
    <x-slot name="header">                <h1 class="font-display text-2xl font-bold text-gray-900">Aktivitas Usaha</h1>
                <p class="mt-1 text-sm text-gray-500">Kelola aktivitas usaha dan kode KBLI di setiap kategori.</p>
            
    </x-slot>

    <x-slot name="actions">
        <x-primary-button :href="route('admin.activities.create', array_filter(['category' => $categoryId]))">+ Tambah Aktivitas</x-primary-button>
    </x-slot>

    <form method="GET" action="{{ route('admin.activities.index') }}" class="mb-5 flex flex-wrap items-center gap-2">
        <x-select name="category" class="w-full sm:w-56" onchange="this.form.submit()" aria-label="Filter kategori">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
            @endforeach
        </x-select>

        <x-admin.search :value="$search" placeholder="Cari nama, slug, atau KBLI..." />

        <x-secondary-button type="submit">Cari</x-secondary-button>

        @if ($search !== '' || $categoryId)
            <a href="{{ route('admin.activities.index') }}" class="px-2 text-sm text-gray-500 hover:text-primary">Reset</a>
        @endif

        <span class="ml-auto text-sm text-gray-500">{{ $activities->total() }} aktivitas</span>
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-100 text-left text-xs font-bold uppercase tracking-wider text-gray-600">
                    <tr>
                        <th class="px-4 py-3">Aktivitas</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3 text-center">Galeri</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
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
                                        <p class="mt-0.5 text-xs text-gray-500">
                                            @if ($activity->kbli_code)
                                                <span class="font-mono">KBLI {{ $activity->kbli_code }}</span> &middot;
                                            @endif
                                            <span class="font-mono">{{ $activity->slug }}</span>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-block whitespace-nowrap rounded-sm bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ $activity->category->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.gallery.show', $activity) }}" class="inline-flex items-center gap-1 text-xs font-semibold {{ $activity->images_count > 0 ? 'text-primary-dark' : 'text-gray-400' }} hover:text-primary" title="Kelola galeri">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="1.5"/><path d="M21 16l-5-5-8 9"/></svg>
                                    {{ $activity->images_count }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                @if ($activity->is_published)
                                    <span class="rounded-sm bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">Terbit</span>
                                @else
                                    <span class="rounded-sm bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.activities.edit', $activity) }}" class="whitespace-nowrap rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:border-primary hover:text-primary">Edit</a>
                                    <form method="POST" action="{{ route('admin.activities.destroy', $activity) }}" onsubmit="return confirm('Hapus aktivitas &quot;{{ e($activity->name) }}&quot; beserta galerinya? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-gray-500 transition hover:text-primary">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-14 text-center text-gray-500">
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
