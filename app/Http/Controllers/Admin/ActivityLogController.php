<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
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

        $logs = ActivityLog::query()
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
            'menus' => ActivityLog::menus(),
            'actions' => ActivityLog::actions(),
        ]);
    }
}
