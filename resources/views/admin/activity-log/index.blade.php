@php
    $filtered = $search !== '' || $menu || $action || $from || $to;
    $hasDate = $from || $to;

    // Data untuk pemilih tanggal (satu tombol ikon): pintasan dan label rentang yang sedang aktif.
    $tz = config('app.timezone');
    $today = now()->timezone($tz)->toDateString();
    $presetData = [
        ['label' => 'Hari ini', 'from' => $today, 'to' => $today],
        ['label' => '7 hari terakhir', 'from' => now()->timezone($tz)->subDays(6)->toDateString(), 'to' => $today],
        ['label' => '30 hari terakhir', 'from' => now()->timezone($tz)->subDays(29)->toDateString(), 'to' => $today],
    ];

    $format = fn (?string $date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('d M Y') : null;
    $rangeLabel = match (true) {
        (bool) ($from && $to && $from === $to) => $format($from),
        (bool) ($from && $to) => $format($from).' – '.$format($to),
        (bool) $from => 'Sejak '.$format($from),
        (bool) $to => 'Sampai '.$format($to),
        default => null,
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Riwayat Aktivitas</h1>
        <p class="mt-1 text-sm text-gray-500">Catatan aksi admin di dashboard: tambah, ubah, hapus, dan lainnya, beserta waktunya.</p>
    </x-slot>

    <div class="rounded-sm border border-gray-200 bg-white shadow-sm">
        <form method="GET" action="{{ route('admin.activity-log.index') }}" class="flex flex-wrap items-center gap-2 border-b border-gray-200 bg-gray-50 px-4 py-3">
            <x-select name="menu" class="w-full sm:w-48" onchange="this.form.submit()" aria-label="Filter menu">
                <option value="">Semua menu</option>
                @foreach ($menus as $item)
                    <option value="{{ $item }}" @selected($menu === $item)>{{ $item }}</option>
                @endforeach
            </x-select>

            <x-select name="action" class="w-full sm:w-44" onchange="this.form.submit()" aria-label="Filter aksi">
                <option value="">Semua aksi</option>
                @foreach ($actions as $key => [$label])
                    <option value="{{ $key }}" @selected($action === $key)>{{ $label }}</option>
                @endforeach
            </x-select>

            <x-admin.search :value="$search" placeholder="Cari keterangan atau admin..." />

            <x-secondary-button type="submit">Cari</x-secondary-button>

            {{-- Filter tanggal: satu tombol ikon; panelnya berisi pintasan, rentang, atau satu hari tertentu --}}
            <div
                class="relative"
                x-data="{
                    open: false,
                    mode: @js($from && $from === $to ? 'day' : 'range'),
                    from: @js($from ?? ''),
                    to: @js($to ?? ''),
                    presets: @js($presetData),
                    max: @js($today),
                    send() { this.$nextTick(() => this.$root.closest('form').submit()); },
                    apply() {
                        if (this.mode === 'day') this.to = this.from;
                        if (this.from && this.to && this.from > this.to) [this.from, this.to] = [this.to, this.from];
                        this.send();
                    },
                    preset(item) { this.mode = item.from === item.to ? 'day' : 'range'; this.from = item.from; this.to = item.to; this.send(); },
                    clear() { this.from = ''; this.to = ''; this.send(); },
                }"
                @keydown.escape="open = false"
                @click.outside="open = false"
            >
                <input type="hidden" name="dari" :value="from">
                <input type="hidden" name="sampai" :value="to">

                <button
                    type="button"
                    @click="open = ! open"
                    :aria-expanded="open"
                    aria-haspopup="dialog"
                    title="Filter tanggal"
                    aria-label="Filter tanggal{{ $rangeLabel ? ': '.$rangeLabel : '' }}"
                    @class([
                        'inline-flex items-center gap-2 rounded border px-3 py-2.5 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-1',
                        'border-primary bg-red-50 text-primary' => $hasDate,
                        'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' => ! $hasDate,
                    ])
                >
                    <svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
                    @if ($rangeLabel)
                        <span class="whitespace-nowrap">{{ $rangeLabel }}</span>
                    @endif
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition.opacity.duration.100ms
                    role="dialog"
                    aria-label="Filter tanggal"
                    class="absolute left-0 top-full z-30 mt-2 w-[19.5rem] max-w-[calc(100vw-2rem)] rounded-sm border border-gray-300 bg-white p-4 shadow-lg"
                >
                    <p class="text-sm font-semibold text-gray-900">Filter tanggal</p>

                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <template x-for="item in presets" :key="item.label">
                            <button
                                type="button"
                                @click="preset(item)"
                                :class="from === item.from && to === item.to ? 'border-primary bg-primary text-white' : 'border-gray-300 bg-white text-gray-600 hover:border-gray-400 hover:bg-gray-50'"
                                class="rounded-sm border px-2.5 py-1.5 text-xs font-medium transition"
                                x-text="item.label"
                            ></button>
                        </template>
                    </div>

                    {{-- Rentang atau satu hari --}}
                    <div class="mt-4 inline-flex rounded-sm border border-gray-300 p-0.5 text-xs font-medium" role="tablist" aria-label="Jenis filter">
                        <button type="button" role="tab" :aria-selected="mode === 'range'" @click="mode = 'range'" :class="mode === 'range' ? 'bg-gray-800 text-white' : 'text-gray-600 hover:bg-gray-100'" class="rounded-sm px-3 py-1.5 transition">Rentang tanggal</button>
                        <button type="button" role="tab" :aria-selected="mode === 'day'" @click="mode = 'day'" :class="mode === 'day' ? 'bg-gray-800 text-white' : 'text-gray-600 hover:bg-gray-100'" class="rounded-sm px-3 py-1.5 transition">1 tanggal</button>
                    </div>

                    <div class="mt-3 grid gap-3" :class="mode === 'range' && 'grid-cols-2'">
                        <label class="block text-xs font-medium text-gray-600">
                            <span x-text="mode === 'day' ? 'Tanggal' : 'Dari'"></span>
                            <input type="date" x-model="from" :max="max" class="mt-1 block w-full rounded-sm border border-gray-300 bg-white px-2.5 py-2 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </label>
                        <label class="block text-xs font-medium text-gray-600" x-show="mode === 'range'">
                            Sampai
                            <input type="date" x-model="to" :max="max" class="mt-1 block w-full rounded-sm border border-gray-300 bg-white px-2.5 py-2 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </label>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-2">
                        <button type="button" x-show="from || to" @click="clear()" class="text-sm font-medium text-gray-500 hover:text-primary">Hapus filter</button>
                        <span x-show="! (from || to)"></span>
                        <button type="button" @click="apply()" :disabled="! from && ! to" class="rounded-sm bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark disabled:cursor-not-allowed disabled:bg-gray-300">Terapkan</button>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:ml-auto">
                @if ($filtered)
                    <a href="{{ route('admin.activity-log.index') }}" class="px-2 text-sm text-gray-500 hover:text-primary">Reset</a>
                @endif
                <span class="px-1 text-sm text-gray-500">{{ $logs->total() }} catatan</span>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-secondary-dark text-center text-xs font-bold uppercase tracking-wider text-white [&>tr>th]:border-b-2 [&>tr>th]:border-primary">
                    <tr>
                        <th class="px-4 py-3">Tanggal &amp; jam</th>
                        <th class="px-4 py-3">Admin</th>
                        <th class="px-4 py-3">Aksi</th>
                        <th class="px-4 py-3">Menu</th>
                        <th class="px-4 py-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        @php($time = $log->created_at->copy()->timezone(config('app.timezone'))->locale('id'))
                        <tr class="transition hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                <p class="font-medium text-gray-900">{{ $time->translatedFormat('d M Y') }}</p>
                                <p class="mt-0.5 text-xs tabular-nums text-gray-500">{{ $time->format('H:i:s') }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-center text-gray-700">{{ $log->user_name }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-sm px-2 py-0.5 text-xs font-medium {{ $log->actionClass() }}">
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $log->actionIcon() }}"/></svg>
                                    {{ $log->actionLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block whitespace-nowrap rounded-sm bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ $log->menu }}</span>
                            </td>
                            <td class="min-w-64 px-4 py-3 text-center text-gray-700">{{ $log->detail }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-500">
                                @if ($filtered)
                                    Tidak ada catatan yang cocok dengan penyaringan ini.
                                @else
                                    Belum ada aktivitas yang tercatat.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-gray-200 bg-gray-50 px-4 py-3">
                {{ $logs->links('pagination.admin', ['label' => 'catatan']) }}
            </div>
        @endif
    </div>
</x-app-layout>
