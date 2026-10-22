<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\IdleSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SessionActivityController extends Controller
{
    /**
     * Dipanggil berkala oleh dashboard selama admin masih aktif di layar (mengetik, menggulir, dst.)
     * agar sesi tidak dianggap diam walau tidak ada halaman yang dibuka.
     */
    public function keepAlive(Request $request): Response
    {
        // Tombol "Tetap masuk" atau aktivitas di tab lain: sesi dilanjutkan dari sekarang.
        $request->session()->forget(IdleSession::WARNING_KEY);
        $request->session()->put(IdleSession::KEY, now()->timestamp);

        return response()->noContent();
    }

    /**
     * Dashboard memberi tahu bahwa peringatan tampil dan sudah berapa detik admin diam. Jam server
     * diselaraskan dengan jam browser, lalu tidak disegarkan lagi oleh permintaan biasa, sehingga
     * pindah halaman atau memuat ulang tidak mengulang hitungan mundur.
     */
    public function idleWarning(Request $request): Response
    {
        $limit = IdleSession::timeoutMinutes() * 60;
        $idle = max(0, min($limit, (int) $request->input('idle', 0)));

        $request->session()->put(IdleSession::KEY, now()->timestamp - $idle);
        $request->session()->put(IdleSession::WARNING_KEY, true);

        return response()->noContent();
    }

    /**
     * Dipanggil dashboard saat pewaktu di browser menyatakan admin diam. Server memastikan dulu
     * (mis. tab lain mungkin masih aktif); hanya bila benar-benar sudah melewati batas, admin keluar.
     */
    public function idleLogout(Request $request): JsonResponse
    {
        $idle = IdleSession::idleSeconds($request);
        $limit = IdleSession::timeoutMinutes() * 60;

        // Toleransi satu menit untuk selisih jam antara browser dan server.
        if ($idle !== null && $idle < $limit - 60) {
            return response()->json(['active' => true]);
        }

        IdleSession::expire($request, $request->user());

        return response()->json(['redirect' => route('login')]);
    }
}
