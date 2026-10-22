<?php

namespace App\Http\Middleware;

use App\Support\IdleSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin yang tidak membuka atau mengirim apa pun ke dashboard selama batas waktu
 * (config admin.idle_timeout) dikeluarkan dan harus masuk kembali.
 */
class AdminIdleTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $idle = IdleSession::idleSeconds($request);

            if ($idle !== null && $idle >= IdleSession::timeoutMinutes() * 60) {
                IdleSession::expire($request, $user);

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Sesi berakhir karena tidak aktif.',
                        'redirect' => route('login'),
                    ], 401);
                }

                return redirect()->route('login');
            }

            // Selama peringatan tampil, berpindah halaman atau memuat ulang tidak melanjutkan sesi;
            // hanya "Tetap masuk" (keep-alive) yang melakukannya.
            if (! $request->session()->get(IdleSession::WARNING_KEY)) {
                $request->session()->put(IdleSession::KEY, now()->timestamp);
            }
        }

        return $next($request);
    }
}
