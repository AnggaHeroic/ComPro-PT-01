@props(['current' => null, 'counts', 'route', 'params' => []])

@php
    $tabs = [
        ['key' => null, 'label' => 'Semua', 'count' => $counts['total'], 'chip' => 'bg-gray-100 text-gray-700'],
        ['key' => 'published', 'label' => 'Terbit', 'count' => $counts['published'], 'chip' => 'bg-green-100 text-green-800'],
        ['key' => 'draft', 'label' => 'Draft', 'count' => $counts['draft'], 'chip' => 'bg-yellow-100 text-yellow-800'],
    ];
@endphp

<nav class="flex flex-wrap gap-1 border-b border-gray-200 bg-white px-3" aria-label="Filter status">
    @foreach ($tabs as $tab)
        @php($active = $current === $tab['key'])

        <a
            href="{{ route($route, array_filter($params + ['status' => $tab['key']], fn ($value) => filled($value))) }}"
            @if ($active) aria-current="page" @endif
            @class([
                '-mb-px inline-flex items-center gap-2 whitespace-nowrap border-b-2 px-3 py-3 text-sm transition-colors',
                'border-primary font-semibold text-gray-900' => $active,
                'border-transparent font-medium text-gray-500 hover:text-gray-800' => ! $active,
            ])
        >
            {{ $tab['label'] }}
            <span class="rounded-sm px-1.5 py-0.5 text-[11px] font-semibold {{ $tab['chip'] }}">{{ $tab['count'] }}</span>
        </a>
    @endforeach
</nav>
