@props(['name' => 'icon', 'selected' => null, 'options' => []])

<fieldset>
    <legend class="mb-1.5 block text-sm font-medium text-gray-700">Ikon dari daftar</legend>

    <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5">
        @foreach ($options as $key => $label)
            <label class="cursor-pointer">
                <input type="radio" name="{{ $name }}" value="{{ $key }}" @checked($selected === $key) class="peer sr-only">
                <span class="flex h-full flex-col items-center gap-1.5 rounded-md border border-gray-200 bg-white px-2 py-3 text-center text-gray-600 transition hover:border-gray-300 peer-checked:border-primary peer-checked:bg-primary-light peer-checked:text-primary-dark peer-focus-visible:ring-2 peer-focus-visible:ring-primary/40">
                    <span class="flex h-8 w-8 items-center justify-center [&>svg]:h-7 [&>svg]:w-7 [&>svg]:translate-y-0">
                        <x-dynamic-component :component="'icons.'.$key" />
                    </span>
                    <span class="text-[11px] leading-tight">{{ $label }}</span>
                </span>
            </label>
        @endforeach
    </div>

    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</fieldset>
