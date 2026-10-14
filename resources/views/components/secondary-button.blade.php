@php
    $tag = $attributes->has('href') ? 'a' : 'button';
    $defaults = ['class' => 'inline-flex items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-400 hover:bg-gray-50 hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 active:translate-y-px disabled:cursor-not-allowed disabled:opacity-60'];

    if ($tag === 'button') {
        $defaults['type'] = 'button';
    }
@endphp

<{{ $tag }} {{ $attributes->merge($defaults) }}>
    {{ $slot }}
</{{ $tag }}>
