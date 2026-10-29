@props([
    'label' => 'Gambar',
    'current' => null,
    'name' => 'image',
    'removeName' => 'remove_image',
    'removeLabel' => 'Hapus gambar',
    'hint' => null,
    'contain' => false,
    // Tombol hapus hanya bila ada gambar unggahan; $fallback = gambar yang tampil setelah dihapus (mis. bawaan).
    'removable' => null,
    'fallback' => null,
])

@php($removable ??= (bool) $current)

<div x-data="{ preview: null, removed: {{ old($removeName) ? 'true' : 'false' }} }">
    <x-input-label :for="$name" :value="$label" />

    <div class="flex items-start gap-4">
        <div @class([
            'flex shrink-0 items-center justify-center overflow-hidden rounded border border-dashed border-gray-300 bg-gray-50',
            'h-24 w-36' => ! $contain,
            'h-20 w-20 bg-white' => $contain,
        ])>
            <template x-if="preview">
                <img :src="preview" alt="Pratinjau baru" @class(['h-full w-full', 'object-cover' => ! $contain, 'object-contain p-2' => $contain])>
            </template>
            <template x-if="! preview && ! removed && @js((bool) $current)">
                <img src="{{ $current }}" alt="Saat ini" @class(['h-full w-full', 'object-cover' => ! $contain, 'object-contain p-2' => $contain])>
            </template>
            @if ($fallback)
                <template x-if="! preview && removed">
                    <img src="{{ $fallback }}" alt="Bawaan" @class(['h-full w-full', 'object-cover' => ! $contain, 'object-contain p-2' => $contain])>
                </template>
            @endif
            <span x-show="! preview && (removed ? @js(! $fallback) : ! @js((bool) $current))" class="px-2 text-center text-xs text-gray-400">Belum ada</span>
        </div>

        <div class="min-w-0 flex-1 space-y-2">
            <input
                x-ref="file"
                id="{{ $name }}"
                type="file"
                name="{{ $name }}"
                accept="image/jpeg,image/png,image/webp"
                @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null; if (preview) removed = false"
                class="block w-full text-sm text-gray-600 file:mr-3 file:rounded file:border file:border-gray-300 file:bg-white file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-50"
            >
            <p class="text-xs text-gray-500">
                {{ $hint ?? 'JPG, PNG, atau WebP, maksimal '.number_format(config('images.max_upload_kb') / 1024, 0).' MB. Otomatis dikonversi ke WebP dan dikompres.' }}
            </p>

            @if ($current && $removable)
                <input type="hidden" name="{{ $removeName }}" :value="removed ? 1 : 0">

                <button
                    type="button"
                    x-show="! removed"
                    @click="removed = true; preview = null; $refs.file.value = ''"
                    class="inline-flex items-center gap-1.5 rounded border border-red-200 bg-white px-2.5 py-1.5 text-xs font-medium text-red-700 transition-colors hover:bg-red-50"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5h6v2m-8 0 1 12h8l1-12"/></svg>
                    {{ $removeLabel }}
                </button>
                <p x-show="removed" x-cloak class="flex flex-wrap items-center gap-2 text-xs text-gray-600">
                    {{ $fallback ? 'Akan kembali ke gambar bawaan saat disimpan.' : 'Akan dihapus saat disimpan.' }}
                    <button type="button" @click="removed = false" class="font-medium text-primary underline-offset-2 hover:underline">Batalkan</button>
                </p>
            @endif
        </div>
    </div>

    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</div>
