<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Mengakhiri sesi admin yang tidak aktif: mencatat riwayat, keluar, lalu menyiapkan
 * pesan untuk halaman login.
 */
class IdleSession
{
    /** Kunci sesi: waktu (detik epoch) permintaan admin terakhir. */
    public const KEY = 'admin_last_activity';

    /** Kunci sesi: peringatan tidak aktif sedang tampil; permintaan biasa tidak lagi menyegarkan KEY. */
    public const WARNING_KEY = 'admin_idle_warning';

    public static function timeoutMinutes(): int
    {
        return max(1, (int) config('admin.idle_timeout', 30));
    }

    /** Berapa detik sejak aktivitas admin terakhir yang tercatat di sesi (null bila belum ada). */
    public static function idleSeconds(Request $request): ?int
    {
        $last = $request->session()->get(self::KEY);

        return $last === null ? null : max(0, now()->timestamp - (int) $last);
    }

    public static function expire(Request $request, Authenticatable $user): void
    {
        $minutes = self::timeoutMinutes();

        ActivityLogger::record(
            ActivityLog::ACTION_TIMEOUT,
            'Akun',
            "Keluar otomatis karena tidak aktif selama {$minutes} menit",
            $user,
        );

        // Catatan "Keluar" biasa tidak perlu menyusul: yang tercatat cukup "Keluar otomatis".
        $request->attributes->set('idle_logout', true);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flash('status', "Anda keluar otomatis karena tidak aktif selama {$minutes} menit. Silakan masuk kembali.");
    }
}
