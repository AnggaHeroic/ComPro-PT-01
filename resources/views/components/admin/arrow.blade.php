@props(['direction' => 'right'])

{{--
    Panah untuk panel admin (menggantikan karakter teks → dan ↗ yang bentuknya berbeda di tiap font).
    Garis dan ujungnya sama seperti ikon "Kembali" (x-admin.back). direction: right | external.
--}}
@php
    $paths = [
        'right' => 'M5 12h14m-6-6 6 6-6 6',
        'external' => 'M7 17 17 7M8 7h9v9',
    ];
@endphp

<svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2.2"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    {{ $attributes->merge(['class' => 'h-4 w-4 shrink-0']) }}
>
    <path d="{{ $paths[$direction] ?? $paths['right'] }}"/>
</svg>
