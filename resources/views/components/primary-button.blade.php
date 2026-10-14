@props(['size' => 'md'])

@php
    $tag = $attributes->has('href') ? 'a' : 'button';
    $sizes = ['md' => 'px-4 py-2.5 text-sm', 'sm' => 'px-3.5 py-1.5 text-[13px]'];

    $defaults = ['class' => 'inline-flex items-center justify-center gap-2 rounded-md bg-primary font-semibold text-white shadow-sm ring-1 ring-inset ring-black/10 transition hover:bg-primary-dark hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 active:translate-y-px disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-400 disabled:shadow-none disabled:ring-gray-200 disabled:hover:bg-gray-200 disabled:hover:shadow-none '.($sizes[$size] ?? $sizes['md'])];

    if ($tag === 'button') {
        $defaults['type'] = 'submit';
    }
@endphp

<{{ $tag }} {{ $attributes->merge($defaults) }}>
    {{ $slot }}
</{{ $tag }}>
