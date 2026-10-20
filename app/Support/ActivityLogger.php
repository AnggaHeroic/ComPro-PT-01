<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Mencatat aksi admin ke riwayat aktivitas. Gagal mencatat tidak boleh menggagalkan aksi
 * yang sebenarnya, jadi kesalahan hanya dilaporkan.
 */
class ActivityLogger
{
    public static function record(string $action, string $menu, ?string $detail = null, ?Authenticatable $user = null): void
    {
        try {
            $user ??= auth()->user();

            ActivityLog::create([
                'user_id' => $user?->getAuthIdentifier(),
                'user_name' => mb_substr((string) ($user->name ?? 'Sistem'), 0, 120),
                'action' => $action,
                'menu' => $menu,
                'detail' => $detail === null ? null : mb_substr($detail, 0, 500),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * Nama kolom yang berubah pada model (setelah save/update), dalam bahasa admin.
     * Kosong bila tidak ada yang berubah.
     *
     * @param  array<string, string>  $labels  kolom => label; kolom lain diabaikan
     */
    public static function changedLabels(Model $model, array $labels): string
    {
        $changed = [];

        foreach (array_keys($model->getChanges()) as $column) {
            if (isset($labels[$column])) {
                $changed[$labels[$column]] = true;
            }
        }

        return implode(', ', array_keys($changed));
    }

    /** Teks status untuk keterangan. */
    public static function status(bool $published): string
    {
        return $published ? 'Terbit' : 'Draft';
    }
}
