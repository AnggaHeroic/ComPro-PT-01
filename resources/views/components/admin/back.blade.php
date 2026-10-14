@props(['href', 'label' => 'Kembali'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'group mb-3 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-primary']) }}>
    <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
    {{ $label }}
</a>
