@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700']) }}>
        {{ $status }}
    </div>
@endif
