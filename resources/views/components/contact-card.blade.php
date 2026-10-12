@props([
    'type' => 'default',
    'title',
    'value',
    'description' => null,
    'href' => null,
    'border' => true,
])

@php
    $icon = match ($type) {
        'email' => 'mail',
        'phone' => 'phone',
        'location' => 'maps',
        default => 'mail',
    };
@endphp

@if($href)
    <a
        href="{{ $href }}"
        @class([
            'group flex gap-4 px-3 py-5 transition-colors duration-200 hover:bg-primary-light/25',
            'border-b border-slate-200' => $border,
        ])
    >
        <div
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-light text-primary transition-colors duration-200 group-hover:bg-primary group-hover:text-white"
        >
            <x-dynamic-component
                :component="'icons.' . $icon"
            />
        </div>

        <div class="min-w-0">
            <p
                class="text-sm font-semibold text-secondary-dark transition-colors duration-200 group-hover:text-primary"
            >
                {{ $title }}
            </p>

            <p class="mt-1 break-words text-sm leading-6 text-slate-600">
                {{ $value }}
            </p>

            @if($description)
                <p class="mt-1 text-xs leading-5 text-slate-500">
                    {{ $description }}
                </p>
            @endif
        </div>

        <x-icons.arrow-right
            class="ml-auto mt-1 h-4 w-4 shrink-0 text-slate-400 transition-colors duration-200 group-hover:text-primary"
        />
    </a>
@else
    <div
        @class([
            'group flex gap-4 px-3 py-5',
            'border-b border-slate-200' => $border,
        ])
    >
        <div
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-light text-primary"
        >
            <x-dynamic-component
                :component="'icons.' . $icon"
            />
        </div>

        <div class="min-w-0">
            <p class="text-sm font-semibold text-secondary-dark">
                {{ $title }}
            </p>

            <p class="mt-1 break-words text-sm leading-6 text-slate-600">
                {{ $value }}
            </p>

            @if($description)
                <p class="mt-1 text-xs leading-5 text-slate-500">
                    {{ $description }}
                </p>
            @endif
        </div>
    </div>
@endif