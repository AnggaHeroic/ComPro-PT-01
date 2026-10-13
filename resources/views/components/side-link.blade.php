@props(['active' => false])

@php
$classes = $active
    ? 'flex items-center gap-3 rounded-md bg-primary-light px-3 py-2 text-sm font-semibold text-primary-dark'
    : 'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if ($active) aria-current="page" @endif>
    {{ $slot }}
</a>
