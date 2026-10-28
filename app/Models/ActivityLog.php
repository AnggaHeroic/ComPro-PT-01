<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris riwayat: siapa melakukan apa, di menu mana, dan kapan.
 */
#[Fillable(['user_id', 'user_name', 'action', 'menu', 'subject', 'detail'])]
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

    public const ACTION_PROFILE = 'profile';

    public const ACTION_LOGIN = 'login';

    public const ACTION_LOGOUT = 'logout';

    public const ACTION_TIMEOUT = 'timeout';

    /**
     * Aksi yang mengubah isi situs (bukan masuk/keluar atau ganti password); dipakai panel
     * "Terakhir diubah" di dashboard.
     *
     * @return list<string>
     */
    public static function contentActions(): array
    {
        return [
            self::ACTION_CREATED,
            self::ACTION_UPDATED,
            self::ACTION_DELETED,
            self::ACTION_REORDERED,
            self::ACTION_RESET,
            self::ACTION_DEFAULT,
        ];
    }

    /**
     * Penanda item yang diubah: "category:12", "activity:5", atau "page:about". Model dipetakan lewat
     * jenis dan id-nya; teks dikembalikan apa adanya.
     */
    public static function subjectKey(Model|string $subject): string
    {
        return match (true) {
            is_string($subject) => $subject,
            $subject instanceof ServiceCategory => 'category:'.$subject->getKey(),
            $subject instanceof BusinessActivity => 'activity:'.$subject->getKey(),
            default => strtolower(class_basename($subject)).':'.$subject->getKey(),
        };
    }

    /** Catatan terakhir yang mengubah (atau membuat) item tersebut, atau null bila belum ada. */
    public static function lastChangeFor(Model|string $subject): ?self
    {
        return self::query()
            ->where('subject', self::subjectKey($subject))
            ->whereIn('action', [self::ACTION_CREATED, self::ACTION_UPDATED, self::ACTION_RESET])
            ->latest('created_at')
            ->latest('id')
            ->first();
    }

    /** Jenis (tab) yang dibuka saat halaman riwayat dibuka tanpa pilihan. */
    public const DEFAULT_GROUP = 'semua';

    /**
     * Jenis catatan di halaman Riwayat Aktivitas, urut dari kiri ke kanan seperti tab: semua, akses akun, perubahan konten. "actions"/"menus" null berarti tanpa batas; menus kosong
     * berarti penyaring menu tidak ditampilkan untuk jenis itu. "owns" = menu yang dimiliki jenis itu, dipakai
     * untuk menentukan tab dari tautan lama yang hanya memuat menu.
     *
     * @return array<string, array{label: string, actions: list<string>|null, menus: list<string>|null, owns: list<string>}>
     */
    public static function groups(): array
    {
        return [
            'semua' => [
                'label' => 'Semua',
                'actions' => null,
                'menus' => null,
                'owns' => [],
            ],
            'akses' => [
                'label' => 'Akses akun',
                'actions' => [self::ACTION_LOGIN, self::ACTION_LOGOUT, self::ACTION_TIMEOUT, self::ACTION_PASSWORD, self::ACTION_PROFILE],
                'menus' => [],
                'owns' => ['Akun', 'Profil'],
            ],
            'konten' => [
                'label' => 'Perubahan konten',
                'actions' => self::contentActions(),
                'menus' => ['Kategori', 'Aktivitas Usaha', 'Galeri', 'Konten Halaman'],
                'owns' => ['Kategori', 'Aktivitas Usaha', 'Galeri', 'Konten Halaman'],
            ],
        ];
    }

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
            self::ACTION_PROFILE => ['Ubah profil', 'bg-teal-100 text-teal-800', 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z'],
            self::ACTION_LOGIN => ['Masuk', 'bg-gray-100 text-gray-700', 'M15 3h4a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-4M10 17l5-5-5-5M15 12H3'],
            self::ACTION_TIMEOUT => ['Keluar otomatis', 'bg-amber-100 text-amber-800', 'M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
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

    /**
     * Keterangan dipecah untuk tampilan: kalimat utama, lalu keterangan tambahan dalam tanda kurung di
     * baris berikutnya, mis. ['Mengubah kategori "X"', '(yang diubah: Nama)']. Tanpa tanda kurung di akhir,
     * seluruh kalimat menjadi baris pertama dan baris kedua null. Teks yang tersimpan tidak berubah.
     *
     * @return array{0: string, 1: string|null}
     */
    public function detailLines(): array
    {
        $detail = trim((string) $this->detail);

        if (preg_match('/^(.+?)\s+(\([^()]+\))$/su', $detail, $match)) {
            return [$match[1], $match[2]];
        }

        return [$detail, null];
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
