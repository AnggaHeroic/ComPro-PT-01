<select {{ $attributes->merge(['class' => 'block rounded border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20']) }}>
    {{ $slot }}
</select>
