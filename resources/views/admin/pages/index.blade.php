@php
    $routes = ['home' => 'home', 'about' => 'about', 'services' => 'services', 'contact' => 'contact'];
    $icons = [
        'home' => 'M3 10.5 12 3l9 7.5M5 9.5V21h14V9.5M9 21v-6h6v6',
        'about' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-8 9c0-4 4-6 8-6s8 2 8 6',
        'services' => 'M3 7l9-4 9 4-9 4-9-4zM3 12l9 4 9-4M3 17l9 4 9-4',
        'contact' => 'M4 6h16v12H4zM4 7l8 6 8-6',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Konten Halaman</h1>
        <p class="mt-1 text-sm text-gray-500">Ubah teks dan foto pada halaman website. Tata letak dan desain tidak berubah.</p>
    </x-slot>

    <div class="mb-6 flex gap-3 rounded border border-gray-200 bg-white p-4 text-sm text-gray-600 shadow-sm">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01M11 12h1v5h1m8-5a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <p>Pilih halaman, lalu <strong class="font-semibold text-gray-900">klik bagian pada pratinjau</strong> atau pilih tab untuk mengubah bagian itu saja. Daftar kategori dan aktivitas usaha dikelola terpisah lewat menu <a href="{{ route('admin.categories.index') }}" class="font-medium text-primary hover:underline">Kategori</a> dan <a href="{{ route('admin.activities.index') }}" class="font-medium text-primary hover:underline">Aktivitas Usaha</a>.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($pages as $key => $page)
            @php
                $last = $lastEdited[$key] ?? null;
                $changed = (int) ($customised[$key] ?? 0);
            @endphp
            <section class="flex flex-col rounded border border-gray-200 bg-white shadow-sm transition-colors hover:border-primary">
                <a href="{{ route('admin.pages.edit', $key) }}" class="group flex flex-1 gap-4 p-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded bg-red-50 text-primary">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icons[$key] ?? $icons['about'] }}"/></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="flex items-center gap-2">
                            <span class="text-base font-semibold text-gray-900 group-hover:text-primary">{{ $page['label'] }}</span>
                            <span class="rounded-sm bg-gray-100 px-1.5 py-0.5 text-[11px] font-medium text-gray-600">{{ $loop->iteration }}</span>
                        </span>
                        <span class="mt-1 block text-sm leading-5 text-gray-500">{{ $page['description'] }}</span>
                    </span>
                </a>

                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 border-t border-gray-100 bg-gray-50 px-5 py-3">
                    <p class="text-xs text-gray-500">
                        {{ count($page['sections']) }} bagian ·
                        @if ($last)
                            Terakhir diubah {{ \Illuminate\Support\Carbon::parse($last)->timezone(config('app.timezone'))->format('d M Y, H:i') }}
                        @else
                            <span class="text-gray-400">Masih memakai teks default</span>
                        @endif
                    </p>

                    <div class="flex items-center gap-2">
                        @if (isset($routes[$key]))
                            <a href="{{ route($routes[$key]) }}" target="_blank" rel="noopener" class="text-xs font-medium text-gray-600 hover:text-primary hover:underline">Lihat</a>
                        @endif
                        <x-primary-button :href="route('admin.pages.edit', $key)" size="sm">Edit konten</x-primary-button>
                    </div>
                </div>
            </section>
        @endforeach
    </div>
</x-app-layout>
