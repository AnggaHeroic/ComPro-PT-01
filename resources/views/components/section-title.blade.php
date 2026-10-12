@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'center' => false,
])

<div
    @class([
        'max-w-2xl',
        'mx-auto text-center' => $center,
    ])
>
    @if($eyebrow)
        <div class="mb-3 flex items-center gap-3 {{ $center ? 'justify-center' : '' }}">
            <span class="h-px w-8 bg-primary"></span>
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary">
                {{ $eyebrow }}
            </span>
        </div>
    @endif
    <h2 class="text-3xl font-bold tracking-tight text-secondary-dark md:text-4xl">
        {{ $title }}
    </h2>
    @if($description)
        <p class="mt-4 text-base leading-7 text-slate-600">
            {{ $description }}
        </p>
    @endif
</div>