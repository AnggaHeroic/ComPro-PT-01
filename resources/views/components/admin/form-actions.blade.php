@props(['cancel', 'label' => 'Simpan'])

<div class="sticky bottom-0 z-10 -mx-4 mt-8 border-t border-gray-300 bg-white/95 px-4 py-3 shadow-[0_-4px_12px_-6px_rgba(0,0,0,0.15)] backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-10 lg:px-10">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-3">
        <p x-data x-show="! $store.unsaved.dirty" class="hidden text-xs text-gray-500 sm:block">Perubahan baru diterapkan ke website setelah Anda menekan Simpan.</p>
        <p x-data x-show="$store.unsaved.dirty" x-cloak class="flex items-center gap-2 text-xs font-medium text-amber-800" role="status">
            <span class="h-2 w-2 shrink-0 rounded-full bg-amber-500" aria-hidden="true"></span>
            Ada perubahan yang belum disimpan
        </p>

        <div class="ml-auto flex items-center gap-3">
            <x-secondary-button :href="$cancel">Batal</x-secondary-button>
            <x-primary-button>{{ $label }}</x-primary-button>
        </div>
    </div>
</div>
