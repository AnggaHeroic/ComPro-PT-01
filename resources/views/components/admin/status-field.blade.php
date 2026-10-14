@props([
    'selected' => 'published',
    'exists' => false,
    'url' => null,
    'noun' => 'item',
])

<fieldset>
    <legend class="mb-1.5 block text-sm font-medium text-gray-700">Status</legend>

    <div class="grid gap-3 sm:grid-cols-2">
        <label class="cursor-pointer">
            <input type="radio" name="status" value="published" class="peer sr-only" @checked($selected === 'published')>
            <span class="flex h-full flex-col rounded border border-gray-300 bg-white p-3.5 transition hover:border-gray-400 peer-checked:border-primary peer-checked:bg-primary-light peer-focus-visible:ring-2 peer-focus-visible:ring-primary/40">
                <span class="text-sm font-semibold text-gray-900">Terbit</span>
                <span class="mt-0.5 text-xs text-gray-500">Tampil di website. URL dibuat otomatis dari nama.</span>
            </span>
        </label>

        <label class="cursor-pointer">
            <input type="radio" name="status" value="draft" class="peer sr-only" @checked($selected === 'draft')>
            <span class="flex h-full flex-col rounded border border-gray-300 bg-white p-3.5 transition hover:border-gray-400 peer-checked:border-gray-700 peer-checked:bg-gray-100 peer-focus-visible:ring-2 peer-focus-visible:ring-gray-400">
                <span class="text-sm font-semibold text-gray-900">Draft</span>
                <span class="mt-0.5 text-xs text-gray-500">Disembunyikan dari website dan belum memiliki URL.</span>
            </span>
        </label>
    </div>

    <x-input-error :messages="$errors->get('status')" class="mt-2" />

    @if ($exists)
        <p class="mt-2 text-xs text-gray-500">
            @if ($url)
                URL {{ $noun }}: <span class="font-mono text-gray-700">{{ $url }}</span>
                <span class="text-gray-400">(tetap sama walau nama diubah)</span>
            @else
                Belum memiliki URL. URL dibuat otomatis dari nama saat {{ $noun }} diterbitkan.
            @endif
        </p>
    @endif
</fieldset>
