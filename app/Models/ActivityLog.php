<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris riwayat: siapa melakukan apa, di menu mana, dan kapan.
 */
#[Fillable(['user_id', 'user_name', 'action', 'menu', 'detail'])]
class ActivityLog extends Model
{
    /** Hanya ada created_at; riwayat tidak pernah diubah. */
    public const UPDATED_AT = null;

    public const ACTION_CREATED = 'created';

    public const ACTION_UPDATED = 'updated';

    public const ACTION_DELETED = 'deleted';

    public const ACTION_REORDERED = 'reordered';

    public const ACTION_RESET = 'reset';

    public const ACTION_DEFAULT = 'default';

    public const ACTION_PASSWORD = 'password';

    public const ACTION_LOGIN = 'login';

    public const ACTION_LOGOUT = 'logout';

    /**
     * Label aksi untuk tampilan: [teks, kelas badge, path ikon (24x24, garis)].
     *
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function actions(): array
    {
        return [
            self::ACTION_CREATED => ['Tambah', 'bg-green-100 text-green-800', 'M12 5v14M5 12h14'],
            self::ACTION_UPDATED => ['Ubah', 'bg-sky-100 text-sky-800', 'M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z'],
            self::ACTION_DELETED => ['Hapus', 'bg-red-100 text-red-800', 'M4 7h16M10 11v6M14 11v6M6 7l1 12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-12M9 7V4h6v3'],
            self::ACTION_REORDERED => ['Urutan', 'bg-gray-200 text-gray-800', 'M8 4v16m0 0-3-3m3 3 3-3M16 20V4m0 0-3 3m3-3 3 3'],
            self::ACTION_RESET => ['Reset default', 'bg-yellow-100 text-yellow-800', 'M3 12a9 9 0 1 0 3-6.7M3 4v5h5'],
            self::ACTION_DEFAULT => ['Setel default', 'bg-indigo-100 text-indigo-800', 'M12 3l2.7 5.5 6 .9-4.4 4.2 1 6L12 16.8 6.7 19.6l1-6L3.3 9.4l6-.9L12 3Z'],
            self::ACTION_PASSWORD => ['Ganti password', 'bg-violet-100 text-violet-800', 'M21 2l-2 2m-7.6 7.6a5.5 5.5 0 1 1-7.8 7.8 5.5 5.5 0 0 1 7.8-7.8Zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3'],
            self::ACTION_LOGIN => ['Masuk', 'bg-gray-100 text-gray-700', 'M15 3h4a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-4M10 17l5-5-5-5M15 12H3'],
            self::ACTION_LOGOUT => ['Keluar', 'bg-gray-100 text-gray-700', 'M9 21H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h4M16 17l5-5-5-5M21 12H9'],
        ];
    }

    /**
     * Daftar menu untuk filter.
     *
     * @return list<string>
     */
    public static function menus(): array
    {
        return ['Kategori', 'Aktivitas Usaha', 'Galeri', 'Konten Halaman', 'Profil', 'Akun'];
    }

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return self::actions()[$this->action][0] ?? ucfirst($this->action);
    }

    public function actionIcon(): string
    {
        return self::actions()[$this->action][2] ?? 'M12 5v14M5 12h14';
    }

    public function actionClass(): string
    {
        return self::actions()[$this->action][1] ?? 'bg-gray-100 text-gray-700';
    }
}
