@props(['subject', 'since' => null, 'created' => null])

{{--
    Keterangan "Terakhir diubah oleh Admin · 2 hari yang lalu" di bawah judul halaman ubah.
    Sumbernya catatan terakhir di Riwayat Aktivitas untuk item ini ($subject). Bila belum ada catatan
    (item lama yang namanya ambigu, atau dibuat lewat seeder), waktu $since dipakai tanpa nama admin.
    $created: waktu dibuat; bila $since sama dengan itu, kata yang dipakai "Dibuat" bukan "Diubah".
--}}
@php
    $log = \App\Models\ActivityLog::lastChangeFor($subject);
    $tz = config('app.timezone');

    if ($log) {
        $time = $log->created_at->copy()->timezone($tz);
        $verb = $log->action === \App\Models\ActivityLog::ACTION_CREATED ? 'Dibuat' : 'Terakhir diubah';
        $who = $log->user_name;
    } elseif ($since) {
        $time = \Illuminate\Support\Carbon::parse($since)->timezone($tz);
        $verb = $created && \Illuminate\Support\Carbon::parse($created)->equalTo(\Illuminate\Support\Carbon::parse($since)) ? 'Dibuat' : 'Terakhir diubah';
        $who = null;
    } else {
        $time = null;
    }
@endphp

@if ($time)
    <p {{ $attributes->merge(['class' => 'mt-0! flex items-center gap-1.5 text-xs text-gray-500 max-lg:whitespace-normal!']) }} data-last-changed>
        <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        <span>
            {{ $verb }}
            @if ($who)
                oleh <span class="font-medium text-gray-700">{{ $who }}</span>
            @endif
            &middot;
            <time datetime="{{ $time->toIso8601String() }}" title="{{ $time->copy()->locale('id')->translatedFormat('d M Y, H:i') }}">{{ $time->copy()->locale('id')->diffForHumans() }}</time>
        </span>
    </p>
@else
    <p {{ $attributes->merge(['class' => 'mt-0! flex items-center gap-1.5 text-xs text-gray-500 max-lg:whitespace-normal!']) }} data-last-changed>
        <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        Belum pernah diubah (memakai teks bawaan)
    </p>
@endif
