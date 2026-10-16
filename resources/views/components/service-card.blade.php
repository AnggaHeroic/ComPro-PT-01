@props([
    'number' => null,
    'icon',
    'iconUrl' => null,
    'title',
    'description',
    'href' => null,
])

<a
    href="{{ $href ?? '#' }}"
    class="group relative flex h-[320px] flex-col border border-slate-300 bg-white p-6 max-sm:h-[196px] max-sm:p-3.5 transition-all duration-300 hover:border-primary hover:bg-primary-light"
>
    {{-- Service Content --}}
    <div>
        @if($number)
            <span class="font-display text-xs font-bold text-primary">
                {{ str_pad($number, 2, '0', STR_PAD_LEFT) }}
            </span>
        @endif

        <h3
            class="font-display mt-4 line-clamp-2 max-w-[240px] max-sm:mt-2 max-sm:text-sm text-xl font-semibold leading-tight tracking-tight text-secondary-dark transition-colors duration-200 group-hover:text-primary sm:text-2xl"
        >
            {{ $title }}
        </h3>

        <p class="mt-3 line-clamp-4 max-w-[280px] text-sm leading-6 text-slate-500 max-sm:mt-1.5 max-sm:line-clamp-3 max-sm:text-xs max-sm:leading-5">
            {{ $description }}
        </p>
    </div>

    {{-- Service Footer --}}
    <div class="mt-auto flex items-end justify-between pt-5 max-sm:pt-2">
        {{-- Icon --}}
        <div
            @class([
                'text-secondary-dark transition-transform duration-300 max-sm:[&_svg]:h-7 max-sm:[&_svg]:w-7',
                'translate-y-1 group-hover:translate-y-0' => $icon === 'gearcog',
                'group-hover:-translate-y-1' => $icon !== 'gearcog',
            ])
        >
            @if ($iconUrl)
                <img
                    src="{{ $iconUrl }}"
                    alt=""
                    class="h-11 w-11 object-contain max-sm:h-7 max-sm:w-7"
                    loading="lazy"
                >
            @else
                <x-dynamic-component
                    :component="'icons.' . $icon"
                />
            @endif
        </div>

        {{-- Arrow --}}
        <span
            class="text-xl max-sm:text-base text-slate-400 transition-all duration-300 group-hover:translate-x-1 group-hover:text-primary"
            aria-hidden="true"
        >
            →
        </span>
    </div>
</a>