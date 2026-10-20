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

    public const ACTION_LOGIN = 'login';

    public const ACTION_LOGOUT = 'logout';

    /**
     * Label aksi untuk tampilan: [teks, kelas badge].
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function actions(): array
    {
        return [
            self::ACTION_CREATED => ['Tambah', 'bg-green-100 text-green-800'],
            self::ACTION_UPDATED => ['Ubah', 'bg-sky-100 text-sky-800'],
            self::ACTION_DELETED => ['Hapus', 'bg-red-100 text-red-800'],
            self::ACTION_REORDERED => ['Urutan', 'bg-gray-200 text-gray-800'],
            self::ACTION_RESET => ['Setelan default', 'bg-yellow-100 text-yellow-800'],
            self::ACTION_LOGIN => ['Masuk', 'bg-gray-100 text-gray-700'],
            self::ACTION_LOGOUT => ['Keluar', 'bg-gray-100 text-gray-700'],
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

    public function actionClass(): string
    {
        return self::actions()[$this->action][1] ?? 'bg-gray-100 text-gray-700';
    }
}
