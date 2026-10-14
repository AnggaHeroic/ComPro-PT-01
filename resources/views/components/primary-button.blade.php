@php
    $tag = $attributes->has('href') ? 'a' : 'button';
    $defaults = ['class' => 'inline-flex items-center justify-center gap-2 rounded-md bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-inset ring-black/5 transition hover:bg-primary-dark hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 active:translate-y-px disabled:cursor-not-allowed disabled:opacity-60'];

    if ($tag === 'button') {
        $defaults['type'] = 'submit';
    }
@endphp

<{{ $tag }} {{ $attributes->merge($defaults) }}>
    {{ $slot }}
</{{ $tag }}>
