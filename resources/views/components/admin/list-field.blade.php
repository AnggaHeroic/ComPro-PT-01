@props([
    'name',
    'items' => [],
    'itemLabel' => 'Butir',
    'max' => 300,
    'maxItems' => 10,
    'minItems' => 1,
])

{{-- Daftar yang bisa ditambah, dihapus, dan diurutkan. Baris kosong dibuang saat disimpan. --}}
<div
    x-data="{
        items: @js(array_values((array) $items)),
        move(from, to) {
            if (to < 0 || to >= this.items.length) return;
            this.items.splice(to, 0, this.items.splice(from, 1)[0]);
        },
    }"
    class="space-y-2"
>
    <template x-for="(item, index) in items" :key="index">
        <div class="flex items-start gap-2 rounded border border-gray-200 bg-gray-50 p-2.5">
            <span class="mt-2 w-5 shrink-0 text-center text-xs font-semibold text-gray-400" x-text="index + 1"></span>

            <textarea
                name="{{ $name }}"
                x-model="items[index]"
                rows="2"
                maxlength="{{ $max }}"
                aria-label="{{ $itemLabel }}"
                class="block min-w-0 flex-1 rounded border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
            ></textarea>

            <div class="flex shrink-0 flex-col gap-1">
                <div class="flex gap-1">
                    <button type="button" @click="move(index, index - 1)" :disabled="index === 0" aria-label="Naikkan" class="rounded border border-gray-300 bg-white p-1 text-gray-500 hover:bg-gray-100 disabled:opacity-30">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 15 6-6 6 6"/></svg>
                    </button>
                    <button type="button" @click="move(index, index + 1)" :disabled="index === items.length - 1" aria-label="Turunkan" class="rounded border border-gray-300 bg-white p-1 text-gray-500 hover:bg-gray-100 disabled:opacity-30">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                    </button>
                </div>
                <button type="button" @click="items.splice(index, 1)" :disabled="items.length <= {{ $minItems }}" aria-label="Hapus {{ $itemLabel }}" class="inline-flex items-center justify-center gap-1 rounded border border-red-200 bg-white px-1.5 py-1 text-xs font-medium text-red-700 hover:bg-red-50 disabled:opacity-30">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5h6v2m-8 0 1 12h8l1-12"/></svg>
                    Hapus
                </button>
            </div>
        </div>
    </template>

    <button
        type="button"
        @click="items.push('')"
        :disabled="items.length >= {{ $maxItems }}"
        class="inline-flex items-center gap-1.5 rounded border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-40"
    >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
        Tambah {{ $itemLabel }}
    </button>
    <p class="text-xs text-gray-500">Maksimal {{ $maxItems }} butir. Butir yang dikosongkan tidak disimpan.</p>
</div>
