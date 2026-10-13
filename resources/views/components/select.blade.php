<select {{ $attributes->merge(['class' => 'block w-full rounded-md border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20']) }}>
    {{ $slot }}
</select>
