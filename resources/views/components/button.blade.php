@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $baseClasses = '
        font-display
        inline-flex
        items-center
        justify-center
        gap-2
        rounded-lg
        px-5
        py-3
        text-sm
        font-semibold
        transition-all
        duration-200
        focus:outline-none
        focus:ring-2
        focus:ring-primary/30
    ';

    $variants = [
        'primary' => '
            bg-primary
            text-white
            hover:bg-primary-dark
            hover:-translate-y-0.5
        ',

        'secondary' => '
            border
            border-slate-300
            bg-white
            text-secondary-dark
            hover:border-primary
            hover:text-primary
        ',

        'ghost' => '
            text-primary
            hover:bg-primary-light
        ',
    ];
    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if($href)
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </a>

@else
    <button
        type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </button>
@endif 