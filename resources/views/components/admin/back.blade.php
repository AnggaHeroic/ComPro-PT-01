@props(['href', 'label' => 'Kembali'])

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'group inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white py-1.5 pl-1.5 pr-3.5 text-[13px] font-semibold text-gray-700 shadow-sm transition hover:border-primary hover:text-primary hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 active:translate-y-px']) }}
>
    <span class="flex h-5 w-5 items-center justify-center rounded bg-gray-100 text-gray-600 transition group-hover:bg-primary group-hover:text-white">
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
    </span>
    {{ $label }}
</a>
