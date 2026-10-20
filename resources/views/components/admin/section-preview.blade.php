@props(['url', 'section'])

{{-- Pratinjau satu bagian halaman publik (bukan seluruh halaman), memakai isian form yang belum disimpan. --}}
<div
    x-data="sectionPreview({ url: @js($url) })"
    x-effect="active === @js($section) && ! loadedOnce && schedule(0)"
    class="mb-8 overflow-hidden rounded-sm border border-gray-300 bg-white"
>
    <div class="flex items-start justify-between gap-4 border-b border-gray-300 bg-gray-50 px-4 py-3">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1">
                <h3 class="text-sm font-semibold text-gray-900">Pratinjau di website</h3>
                <span x-show="dirty" x-cloak class="inline-flex items-center gap-1 rounded-sm bg-amber-100 px-2 py-0.5 text-[11px] font-medium text-amber-800">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500" aria-hidden="true"></span>
                    Belum disimpan
                </span>
            </div>
            <p class="mt-0.5 text-xs leading-5 text-gray-500">Tampilan bagian yang dikelola di halaman publik. Pratinjau ikut berubah saat Anda menyimpan perubahan.</p>
        </div>

        <div class="flex shrink-0 items-center gap-1">
            <button type="button" @click="schedule(0)" class="inline-flex items-center gap-1.5 rounded px-2 py-1 text-xs font-medium text-gray-600 transition hover:bg-gray-200 hover:text-gray-900">
                <svg class="h-3.5 w-3.5" :class="loading && 'animate-spin'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12a8 8 0 1 1-2.3-5.7M20 4v5h-5"/></svg>
                Muat ulang
            </button>
            <button type="button" @click="open = ! open" :aria-expanded="open" class="rounded px-2 py-1 text-xs font-medium text-gray-600 transition hover:bg-gray-200 hover:text-gray-900" x-text="open ? 'Sembunyikan' : 'Tampilkan'"></button>
        </div>
    </div>

    <div
        x-show="open"
        x-ref="box"
        class="relative overflow-hidden bg-slate-100"
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

        <div x-show="loading" x-cloak class="absolute inset-0 flex items-center justify-center bg-white/70 text-xs font-medium text-gray-500">Menyiapkan pratinjau…</div>
        <div x-show="error" x-cloak class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-white text-xs text-gray-600">
            <p>Pratinjau tidak dapat ditampilkan.</p>
            <button type="button" @click="schedule(0)" class="rounded border border-gray-300 px-2.5 py-1 font-medium text-gray-700 hover:bg-gray-50">Coba lagi</button>
        </div>
    </div>

    <p x-show="open" class="border-t border-gray-300 bg-gray-50 px-4 py-2.5 text-[11px] leading-4 text-gray-500">Foto yang baru Anda pilih tampil di website setelah Anda menekan Simpan.</p>
</div>
