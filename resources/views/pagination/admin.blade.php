{{--
    Pagination untuk tabel admin: teks Indonesia, tombol datar bersudut siku, halaman aktif berwarna utama.
    Pakai: {{ $items->links('pagination.admin', ['label' => 'catatan']) }}  ("label" = nama data, bawaan "data").
--}}
@php
    $label = $label ?? 'data';
    $buttonBase = 'h-9 min-w-9 items-center justify-center border px-3 text-sm font-medium transition focus:outline-none focus-visible:z-10 focus-visible:ring-2 focus-visible:ring-primary';
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500">
            Menampilkan <span class="font-medium text-gray-700">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
            dari <span class="font-medium text-gray-700">{{ $paginator->total() }}</span> {{ $label }}
        </p>

        <div class="flex items-center justify-between gap-3 sm:justify-end">
            {{-- Ringkasan halaman untuk layar kecil --}}
            <span class="text-sm text-gray-500 sm:hidden">Halaman <span class="font-medium text-gray-700">{{ $paginator->currentPage() }}</span> dari {{ $paginator->lastPage() }}</span>

            <div class="inline-flex -space-x-px">
                {{-- Sebelumnya --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="Halaman sebelumnya" class="{{ $buttonBase }} inline-flex cursor-not-allowed rounded-l-sm border-gray-200 bg-gray-50 text-gray-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 5-7 7 7 7"/></svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya" class="{{ $buttonBase }} inline-flex rounded-l-sm border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 5-7 7 7 7"/></svg>
                    </a>
                @endif

                {{-- Nomor halaman (disembunyikan di layar kecil) --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="{{ $buttonBase }} hidden border-gray-300 bg-white text-gray-400 sm:inline-flex">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="{{ $buttonBase }} relative z-10 hidden border-primary bg-primary text-white sm:inline-flex">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}" class="{{ $buttonBase }} hidden border-gray-300 bg-white text-gray-700 hover:bg-gray-50 sm:inline-flex">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Berikutnya --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya" class="{{ $buttonBase }} inline-flex rounded-r-sm border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                    </a>
                @else
                    <span aria-disabled="true" aria-label="Halaman berikutnya" class="{{ $buttonBase }} inline-flex cursor-not-allowed rounded-r-sm border-gray-200 bg-gray-50 text-gray-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
