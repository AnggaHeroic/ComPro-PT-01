@props(['title' => null, 'description' => null])

<section {{ $attributes->merge(['class' => 'rounded border border-gray-200 bg-white shadow-sm']) }}>
    @if ($title)
        <header class="rounded-t border-b border-gray-200 bg-gray-50 px-6 py-4 sm:px-7">
            <h2 class="text-base font-semibold text-gray-900">{{ $title }}</h2>
            @if ($description)
                <p class="mt-0.5 text-sm text-gray-500">{{ $description }}</p>
            @endif
        </header>
    @endif

    @if (! $slot->isEmpty())
        <div class="p-6 sm:p-7">
            {{ $slot }}
        </div>
    @endif
</section>
