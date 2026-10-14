@props(['size' => 'md'])

@php
    $tag = $attributes->has('href') ? 'a' : 'button';
    $sizes = ['md' => 'px-4 py-2.5 text-sm', 'sm' => 'px-3.5 py-1.5 text-[13px]'];

    $defaults = ['class' => 'inline-flex items-center justify-center gap-2 rounded border border-gray-300 bg-white font-semibold text-gray-700 shadow-sm transition hover:border-gray-400 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 active:translate-y-px disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 disabled:shadow-none '.($sizes[$size] ?? $sizes['md'])];

    if ($tag === 'button') {
        $defaults['type'] = 'button';
    }
@endphp

<{{ $tag }} {{ $attributes->merge($defaults) }}>
    {{ $slot }}
</{{ $tag }}>
