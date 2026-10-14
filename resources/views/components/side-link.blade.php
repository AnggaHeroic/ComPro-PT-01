@props(['active' => false])

@php
$classes = $active
    ? 'flex items-center gap-3 rounded-md bg-primary px-3 py-2.5 text-sm font-semibold text-white shadow-sm shadow-black/20'
    : 'flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} @if ($active) aria-current="page" @endif>
    {{ $slot }}
</a>
