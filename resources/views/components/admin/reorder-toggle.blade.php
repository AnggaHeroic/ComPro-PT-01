@props(['active' => false, 'href' => '#', 'disabled' => false, 'reason' => null])

@if ($disabled)
    <span
        role="switch"
        aria-checked="false"
        aria-disabled="true"
        title="{{ $reason }}"
        class="inline-flex cursor-not-allowed items-center gap-2 text-sm font-medium text-gray-400"
    >
        <span class="relative h-5 w-9 rounded-full bg-gray-200">
            <span class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white"></span>
        </span>
        Atur urutan
    </span>
@else
    <a
        {{ $attributes }}
        href="{{ $href }}"
        role="switch"
        aria-checked="{{ $active ? 'true' : 'false' }}"
        title="{{ $active ? 'Matikan mode atur urutan' : 'Aktifkan mode atur urutan (seret baris untuk mengubah urutan)' }}"
        class="group inline-flex items-center gap-2 text-sm font-medium text-gray-700 transition-colors hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
    >
        <span @class([
            'relative h-5 w-9 rounded-full transition-colors',
            'bg-primary' => $active,
            'bg-gray-300 group-hover:bg-gray-400' => ! $active,
        ])>
            <span @class([
                'absolute top-0.5 h-4 w-4 rounded-full bg-white shadow-sm transition-all',
                'left-[1.125rem]' => $active,
                'left-0.5' => ! $active,
            ])></span>
        </span>
        Atur urutan
    </a>
@endif
