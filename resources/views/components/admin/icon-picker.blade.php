@props(['name' => 'icon', 'selected' => null, 'options' => []])

<div
    x-data="{
        open: false,
        value: @js($selected),
        labels: @js($options),
        choose(key) { this.value = key; this.open = false; },
    }"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
    class="relative"
>
    <span class="mb-1.5 block text-sm font-medium text-gray-700">Ikon dari daftar</span>

    <input type="hidden" name="{{ $name }}" :value="value">

    {{-- Tombol: menampilkan ikon yang sedang dipilih --}}
    <button
        type="button"
        @click="open = ! open"
        :aria-expanded="open"
        aria-haspopup="listbox"
        class="inline-flex items-center gap-3 rounded-md border border-gray-300 bg-white py-2 pl-2.5 pr-3 text-sm text-gray-700 transition hover:border-gray-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
    >
        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-primary-light text-primary-dark [&_svg]:h-5 [&_svg]:w-5 [&_svg]:translate-y-0">
            @foreach ($options as $key => $label)
                <span x-show="value === '{{ $key }}'" x-cloak class="contents">
                    <x-dynamic-component :component="'icons.'.$key" />
                </span>
            @endforeach
        </span>
        <span x-text="labels[value] ?? 'Pilih ikon'" class="min-w-24 text-left font-medium"></span>
        <x-icons.arrow-down class="text-gray-400 transition" x-bind:class="open && 'rotate-180'" />
    </button>

    {{-- Daftar ikon: hanya ikon, nama tampil sebagai tooltip --}}
    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.100ms
        role="listbox"
        aria-label="Pilih ikon"
        class="absolute left-0 z-20 mt-2 grid w-78 grid-cols-7 gap-1 rounded-md border border-gray-200 bg-white p-2 shadow-md"
    >
        @foreach ($options as $key => $label)
            <button
                type="button"
                role="option"
                title="{{ $label }}"
                aria-label="{{ $label }}"
                :aria-selected="value === '{{ $key }}'"
                @click="choose('{{ $key }}')"
                :class="value === '{{ $key }}' ? 'bg-primary-light text-primary-dark ring-1 ring-primary' : 'text-gray-600 hover:bg-gray-100'"
                class="flex h-10 w-10 items-center justify-center rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 [&_svg]:h-5 [&_svg]:w-5 [&_svg]:translate-y-0"
            >
                <x-dynamic-component :component="'icons.'.$key" />
            </button>
        @endforeach
    </div>

    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</div>
