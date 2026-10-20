<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Riwayat aksi admin, terbaru di atas. Bisa disaring menurut menu dan aksi, atau dicari.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $menu = in_array($request->query('menu'), ActivityLog::menus(), true) ? $request->query('menu') : null;
        $action = array_key_exists((string) $request->query('action'), ActivityLog::actions()) ? $request->query('action') : null;

        // Rentang tanggal (zona waktu aplikasi). Tanggal tidak valid diabaikan; bila terbalik, ditukar.
        $from = $this->date($request->query('dari'));
        $to = $this->date($request->query('sampai'));

        if ($from && $to && $from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        $logs = ActivityLog::query()
            ->when($from, fn ($query) => $query->where('created_at', '>=', $from->copy()->startOfDay()))
            ->when($to, fn ($query) => $query->where('created_at', '<=', $to->copy()->endOfDay()))
            ->when($menu, fn ($query) => $query->where('menu', $menu))
            ->when($action, fn ($query) => $query->where('action', $action))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('detail', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%");
            }))
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity-log.index', [
            'logs' => $logs,
            'search' => $search,
            'menu' => $menu,
            'action' => $action,
            'from' => $from?->toDateString(),
            'to' => $to?->toDateString(),
            'menus' => ActivityLog::menus(),
            'actions' => ActivityLog::actions(),
        ]);
    }

    private function date(mixed $value): ?Carbon
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value, config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }

        // createFromFormat menggeser tanggal yang mustahil (mis. 31 Februari); tolak yang berubah.
        return $date && $date->toDateString() === $value ? $date : null;
    }
}
