@props(['title' => null, 'description' => null])

<section {{ $attributes->merge(['class' => 'rounded-md border border-gray-200 bg-white p-6']) }}>
    @if ($title)
        <header class="{{ $slot->isEmpty() ? '' : 'mb-5' }}">
            <h2 class="text-base font-semibold text-gray-900">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
            @endif
        </header>
    @endif

    {{ $slot }}
</section>
