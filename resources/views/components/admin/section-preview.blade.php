@props(['url', 'section'])

{{-- Pratinjau satu bagian halaman publik (bukan seluruh halaman), memakai isian form yang belum disimpan. --}}
<div
    x-data="sectionPreview({ url: @js($url) })"
    x-effect="active === @js($section) && ! loadedOnce && schedule(0)"
    class="mb-6 rounded border border-dashed border-gray-300 bg-white"
>
    <div class="flex items-center justify-between gap-3 px-3.5 py-2.5">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Pratinjau bagian ini</p>
            <p class="text-xs text-gray-400">Hanya bagian yang sedang diedit; diperbarui otomatis saat Anda mengetik.</p>
        </div>

        <div class="flex shrink-0 items-center gap-1">
            <button type="button" @click="schedule(0)" title="Perbarui pratinjau" aria-label="Perbarui pratinjau" class="rounded p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                <svg class="h-4 w-4" :class="loading && 'animate-spin'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12a8 8 0 1 1-2.3-5.7M20 4v5h-5"/></svg>
            </button>
            <button type="button" @click="open = ! open" :aria-expanded="open" class="rounded px-2 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900" x-text="open ? 'Sembunyikan' : 'Tampilkan'"></button>
        </div>
    </div>

    <div
        x-show="open"
        x-ref="box"
        class="relative overflow-hidden border-t border-dashed border-gray-300 bg-slate-100"
        :style="{ height: Math.max(height * scale, 120) + 'px' }"
    >
        <iframe
            x-ref="frame"
            title="Pratinjau bagian"
            tabindex="-1"
            class="pointer-events-none absolute left-0 top-0 w-[1280px] origin-top-left border-0 bg-white"
            :style="{ transform: `scale(${scale})` }"
            @load="measure()"
        ></iframe>

        <div x-show="loading" x-cloak class="absolute inset-0 flex items-center justify-center bg-white/60 text-xs font-medium text-gray-500">Memuat pratinjau…</div>
        <div x-show="error" x-cloak class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-white text-xs text-gray-600">
            <p>Pratinjau gagal dimuat.</p>
            <button type="button" @click="schedule(0)" class="rounded border border-gray-300 px-2.5 py-1 font-medium text-gray-700 hover:bg-gray-50">Coba lagi</button>
        </div>
    </div>

    <p x-show="open" class="border-t border-dashed border-gray-300 px-3.5 py-2 text-[11px] leading-4 text-gray-400">Foto yang baru dipilih belum tampil di pratinjau sampai disimpan.</p>
</div>
