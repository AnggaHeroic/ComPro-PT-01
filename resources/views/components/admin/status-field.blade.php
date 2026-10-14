@props(['selected' => 'published'])

<fieldset>
    <legend class="mb-1.5 block text-sm font-medium text-gray-700">Status</legend>

    <div class="grid gap-3 sm:grid-cols-2">
        <label class="cursor-pointer">
            <input type="radio" name="status" value="published" class="peer sr-only" @checked($selected === 'published')>
            <span class="flex h-full flex-col rounded border border-gray-300 bg-white p-3.5 transition hover:border-green-500 hover:bg-green-50 peer-checked:border-green-600 peer-checked:bg-green-50 peer-focus-visible:ring-2 peer-focus-visible:ring-green-500/40">
                <span class="text-sm font-semibold text-gray-900">Terbit</span>
                <span class="mt-0.5 text-xs text-gray-500">Tampil di website. Alamat URL dibuat otomatis dari nama bila dikosongkan.</span>
            </span>
        </label>

        <label class="cursor-pointer">
            <input type="radio" name="status" value="draft" class="peer sr-only" @checked($selected === 'draft')>
            <span class="flex h-full flex-col rounded border border-gray-300 bg-white p-3.5 transition hover:border-yellow-500 hover:bg-yellow-50 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 peer-focus-visible:ring-2 peer-focus-visible:ring-yellow-500/40">
                <span class="text-sm font-semibold text-gray-900">Draft</span>
                <span class="mt-0.5 text-xs text-gray-500">Disembunyikan dari website. Mudah ditemukan di tab Draft pada daftar.</span>
            </span>
        </label>
    </div>

    <x-input-error :messages="$errors->get('status')" class="mt-2" />
</fieldset>
