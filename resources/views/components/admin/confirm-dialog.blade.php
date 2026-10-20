{{-- Dialog konfirmasi global; dikendalikan oleh Alpine.store('confirm') di app.js. --}}
<div
    x-data
    x-show="$store.confirm.open"
    x-cloak
    x-effect="$store.confirm.open && $nextTick(() => ($store.confirm.hideCancel ? $refs.confirm : $refs.cancel).focus())"
    @keydown.escape.window="$store.confirm.open && $store.confirm.answer(false)"
    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
    role="alertdialog"
    aria-modal="true"
    aria-labelledby="confirm-title"
    aria-describedby="confirm-message"
>
    <div
        x-show="$store.confirm.open"
        x-transition.opacity
        @click="$store.confirm.answer(false)"
        class="absolute inset-0 bg-black/60"
        aria-hidden="true"
    ></div>

    <div
        x-show="$store.confirm.open"
        x-transition:enter="transition duration-150 ease-out"
        x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        class="relative w-full max-w-md rounded border border-gray-200 bg-white shadow-xl"
    >
        <button
            type="button"
            x-show="$store.confirm.hideCancel"
            @click="$store.confirm.answer(false)"
            aria-label="Tutup"
            class="absolute right-3 top-3 rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/></svg>
        </button>

        <div class="flex gap-4 p-6">
            <span
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                :class="$store.confirm.tone === 'danger' ? 'bg-red-100 text-primary' : 'bg-gray-100 text-secondary-dark'"
                aria-hidden="true"
            >
                <svg x-show="$store.confirm.tone === 'danger'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 2.4 17.5A2 2 0 0 0 4.1 20.5h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                <svg x-show="$store.confirm.tone !== 'danger'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01M11 12h1v5h1m8-5a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </span>

            <div class="min-w-0">
                <h2 id="confirm-title" class="text-base font-semibold text-gray-900" x-text="$store.confirm.title"></h2>
                <p id="confirm-message" class="mt-1.5 text-sm leading-6 text-gray-600" x-text="$store.confirm.message"></p>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-gray-200 bg-gray-50 px-6 py-3 sm:flex-row sm:justify-end">
            <button
                type="button"
                x-ref="cancel"
                x-show="! $store.confirm.hideCancel"
                @click="$store.confirm.answer(false)"
                class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
            >Batal</button>
            <button
                type="button"
                x-show="$store.confirm.secondaryLabel"
                @click="$store.confirm.answer('secondary')"
                class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
                x-text="$store.confirm.secondaryLabel"
            ></button>
            <button
                type="button"
                x-ref="confirm"
                @click="$store.confirm.answer(true)"
                class="rounded bg-primary px-4 py-2 text-sm font-semibold text-white ring-1 ring-inset ring-black/10 hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
                x-text="$store.confirm.label"
            ></button>
        </div>
    </div>
</div>
