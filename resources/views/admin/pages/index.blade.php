<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Konten Halaman</h1>
        <p class="mt-1 text-sm text-gray-500">Ubah teks pada halaman website. Tata letak dan desain tidak berubah.</p>
    </x-slot>

    <div class="max-w-3xl space-y-3">
        @foreach ($pages as $key => $page)
            <a href="{{ route('admin.pages.edit', $key) }}" class="group flex items-center justify-between gap-4 rounded border border-gray-200 bg-white p-5 shadow-sm transition-colors hover:border-primary">
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-gray-900 group-hover:text-primary">{{ $page['label'] }}</h2>
                    <p class="mt-0.5 text-sm text-gray-500">{{ $page['description'] }}</p>
                    <p class="mt-2 text-xs text-gray-400">{{ count($page['sections']) }} bagian dapat diubah</p>
                </div>
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
            </a>
        @endforeach

        <p class="pt-2 text-xs text-gray-500">Halaman Tentang Kami dan Kontak Kami akan ditambahkan pada tahap berikutnya.</p>
    </div>
</x-app-layout>
