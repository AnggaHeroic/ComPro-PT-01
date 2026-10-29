<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Riwayat aksi admin, terbaru di atas, dibagi tiga jenis (tab): semua, akses akun, dan perubahan konten.
     * Tiap jenis bisa disaring menurut menu dan aksi, dicari, atau dibatasi tanggal.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $groups = ActivityLog::groups();

        $menu = in_array($request->query('menu'), ActivityLog::menus(), true) ? $request->query('menu') : null;
        $action = array_key_exists((string) $request->query('action'), ActivityLog::actions()) ? $request->query('action') : null;

        $group = $this->group($request->query('jenis'), $menu, $action);
        $definition = $groups[$group];

        // Penyaring hanya berlaku bila termasuk dalam jenis yang sedang dibuka.
        $action = $action && ($definition['actions'] === null || in_array($action, $definition['actions'], true)) ? $action : null;
        $menu = $menu && $definition['menus'] !== [] && ($definition['menus'] === null || in_array($menu, $definition['menus'], true)) ? $menu : null;

        // Rentang tanggal (zona waktu aplikasi). Tanggal tidak valid diabaikan; bila terbalik, ditukar.
        $from = $this->date($request->query('dari'));
        $to = $this->date($request->query('sampai'));

        if ($from && $to && $from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        // Dasar bersama untuk daftar dan hitungan di tab: tanggal dan pencarian.
        $base = fn (): Builder => ActivityLog::query()
            ->when($from, fn ($query) => $query->where('created_at', '>=', $from->copy()->startOfDay()))
            ->when($to, fn ($query) => $query->where('created_at', '<=', $to->copy()->endOfDay()))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('detail', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%");
            }));

        $counts = [];
        foreach ($groups as $key => $item) {
            $counts[$key] = $base()->when($item['actions'] !== null, fn ($query) => $query->whereIn('action', $item['actions']))->count();
        }

        $logs = $base()
            ->when($definition['actions'] !== null, fn ($query) => $query->whereIn('action', $definition['actions']))
            ->when($menu, fn ($query) => $query->where('menu', $menu))
            ->when($action, fn ($query) => $query->where('action', $action))
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity-log.index', [
            'logs' => $logs,
            'search' => $search,
            'group' => $group,
            'groups' => $groups,
            'counts' => $counts,
            'menu' => $menu,
            'action' => $action,
            'from' => $from?->toDateString(),
            'to' => $to?->toDateString(),
            // Pilihan penyaring mengikuti jenis yang sedang dibuka.
            'menus' => $definition['menus'] ?? ActivityLog::menus(),
            'actions' => array_filter(
                ActivityLog::actions(),
                fn (string $key) => $definition['actions'] === null || in_array($key, $definition['actions'], true),
                ARRAY_FILTER_USE_KEY,
            ),
        ]);
    }

    /**
     * Jenis yang dibuka. Tanpa parameter "jenis", tautan lama dengan menu/aksi tertentu tetap membuka
     * jenis yang memuatnya; selain itu tampilan awalnya semua catatan.
     */
    private function group(mixed $requested, ?string $menu, ?string $action): string
    {
        $groups = ActivityLog::groups();

        if (is_string($requested) && isset($groups[$requested])) {
            return $requested;
        }

        foreach (['konten', 'akses'] as $key) {
            if ($action && in_array($action, $groups[$key]['actions'], true)) {
                return $key;
            }
        }

        foreach (['konten', 'akses'] as $key) {
            if ($menu && in_array($menu, $groups[$key]['owns'], true)) {
                return $key;
            }
        }

        return ActivityLog::DEFAULT_GROUP;
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
