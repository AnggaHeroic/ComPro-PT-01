@props(['href', 'label' => 'Kembali'])

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'group inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white py-2 pl-2 pr-3.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-primary hover:text-primary hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 active:translate-y-px']) }}
>
    <span class="flex h-6 w-6 items-center justify-center rounded bg-gray-100 text-gray-600 transition group-hover:bg-primary group-hover:text-white">
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
    </span>
    {{ $label }}
</a>
