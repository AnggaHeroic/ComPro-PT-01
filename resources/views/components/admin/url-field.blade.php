@props(['value' => null, 'prefix', 'exists' => false, 'published' => false])

<div>
    <x-input-label for="slug" value="Alamat URL" />

    <div class="flex">
        <span class="inline-flex shrink-0 items-center rounded-l border border-r-0 border-gray-300 bg-gray-50 px-3 font-mono text-xs text-gray-500">{{ $prefix }}</span>
        <x-text-input
            id="slug"
            name="slug"
            type="text"
            :value="old('slug', $value)"
            maxlength="150"
            placeholder="otomatis dari nama"
            class="font-mono"
            style="border-top-left-radius: 0; border-bottom-left-radius: 0"
        />
    </div>

    <x-input-error :messages="$errors->get('slug')" class="mt-2" />

    @if ($exists && $published && filled($value))
        <p class="mt-1.5 text-xs text-amber-700">Mengubah alamat ini membuat tautan lama tidak berfungsi (halaman tidak ditemukan).</p>
    @else
        <p class="mt-1.5 text-xs text-gray-500">Kosongkan agar dibuat otomatis dari nama saat diterbitkan; draft belum memiliki alamat. Hanya huruf kecil, angka, dan tanda hubung.</p>
    @endif
</div>
