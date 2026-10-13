@props([
    'label' => 'Gambar',
    'current' => null,
    'name' => 'image',
    'removeName' => 'remove_image',
    'removeLabel' => 'Hapus gambar saat ini',
    'hint' => null,
    'contain' => false,
])

<div x-data="{ preview: null, removed: {{ old($removeName) ? 'true' : 'false' }} }">
    <x-input-label :for="$name" :value="$label" />

    <div class="flex items-start gap-4">
        <div @class([
            'flex shrink-0 items-center justify-center overflow-hidden rounded-md border border-dashed border-gray-300 bg-gray-50',
            'h-24 w-36' => ! $contain,
            'h-20 w-20 bg-white' => $contain,
        ])>
            <template x-if="preview">
                <img :src="preview" alt="Pratinjau baru" @class(['h-full w-full', 'object-cover' => ! $contain, 'object-contain p-2' => $contain])>
            </template>
            <template x-if="! preview && ! removed && @js((bool) $current)">
                <img src="{{ $current }}" alt="Saat ini" @class(['h-full w-full', 'object-cover' => ! $contain, 'object-contain p-2' => $contain])>
            </template>
            <span x-show="! preview && (removed || ! @js((bool) $current))" class="px-2 text-center text-xs text-gray-400">Belum ada</span>
        </div>

        <div class="min-w-0 flex-1 space-y-2">
            <input
                id="{{ $name }}"
                type="file"
                name="{{ $name }}"
                accept="image/jpeg,image/png,image/webp"
                @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border file:border-gray-300 file:bg-white file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-50"
            >
            <p class="text-xs text-gray-500">
                {{ $hint ?? 'JPG, PNG, atau WebP, maksimal '.number_format(config('images.max_upload_kb') / 1024, 0).' MB. Otomatis dikonversi ke WebP dan dikompres.' }}
            </p>

            @if ($current)
                <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="{{ $removeName }}" value="1" x-model="removed" class="h-4 w-4 rounded border-gray-300 accent-primary">
                    {{ $removeLabel }}
                </label>
            @endif
        </div>
    </div>

    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</div>
