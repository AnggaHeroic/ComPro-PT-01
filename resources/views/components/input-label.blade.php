@props(['value'])

<label {{ $attributes->merge(['class' => 'mb-1.5 block text-sm font-medium text-gray-700']) }}>
    {{ $value ?? $slot }}
</label>
