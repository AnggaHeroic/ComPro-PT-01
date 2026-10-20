@php
    $filtered = $search !== '' || $menu || $action;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-gray-900">Riwayat Aktivitas</h1>
        <p class="mt-1 text-sm text-gray-500">Catatan aksi admin di dashboard: tambah, ubah, hapus, dan lainnya, beserta waktunya.</p>
    </x-slot>

    <div class="overflow-hidden rounded border border-gray-200 bg-white shadow-sm">
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

            <div class="flex w-full items-center gap-2 sm:w-auto">
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
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
