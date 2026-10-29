<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Menyimpan gambar upload sebagai WebP yang sudah dikompres dan menghapus
 * file lama dengan aman. Path yang disimpan di database selalu relatif
 * terhadap disk, tidak pernah berasal dari input pengguna.
 */
class ImageUploader
{
    /**
     * Kolom yang menyimpan path gambar; dipakai untuk memeriksa referensi
     * sebelum file dihapus. Tambahkan di sini bila ada tabel baru.
     *
     * @var list<array{0: string, 1: string}>
     */
    private const REFERENCES = [
        ['service_categories', 'image_path'],
        ['service_categories', 'icon_image_path'],
        ['business_activities', 'image_path'],
        ['activity_images', 'image_path'],
        ['page_contents', 'value'],
        ['page_content_defaults', 'value'],
    ];

    /**
     * Aturan validasi standar untuk field upload gambar.
     *
     * @return list<string>
     */
    public static function rules(): array
    {
        return [
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:'.config('images.max_upload_kb'),
            'dimensions:max_width='.config('images.max_dimension').',max_height='.config('images.max_dimension'),
        ];
    }

    /**
     * Konversi ke WebP, perkecil bila terlalu lebar, lalu simpan.
     *
     * @return string path relatif terhadap disk (mis. activities/uuid.webp)
     */
    public function store(UploadedFile $file, string $directory, ?int $maxWidth = null): string
    {
        $directory = trim($directory, '/');

        if ($directory === '' || ! preg_match('#^[a-z0-9_/-]+$#i', $directory)) {
            throw new InvalidArgumentException('Direktori gambar tidak valid.');
        }

        $maxWidth ??= (int) config('images.max_width');
        $source = file_get_contents($file->getRealPath());

        if ($source === false || @getimagesizefromstring($source) === false) {
            throw new InvalidArgumentException('File bukan gambar yang valid.');
        }

        [$webp, $resized] = $this->encode($source, $file->getRealPath(), $maxWidth);

        // Sumber yang sudah WebP dan tidak diubah ukurannya tidak boleh menjadi lebih besar.
        if (! $resized && $this->isWebp($source) && strlen($webp) >= strlen($source)) {
            $webp = $source;
        }

        $path = $directory.'/'.Str::uuid()->toString().'.webp';

        if (! Storage::disk(config('images.disk'))->put($path, $webp)) {
            throw new RuntimeException('Gagal menyimpan gambar ke penyimpanan.');
        }

        return $path;
    }

    /**
     * Hapus file lokal hanya bila tidak lagi direferensikan database.
     * URL eksternal (placeholder) dan nilai kosong diabaikan.
     * Panggil SETELAH baris database diperbarui/dihapus.
     */
    public function release(?string $path): bool
    {
        if (blank($path) || preg_match('#^https?://#i', $path) || str_contains($path, '..')) {
            return false;
        }

        foreach (self::REFERENCES as [$table, $column]) {
            if (DB::table($table)->where($column, $path)->exists()) {
                return false;
            }
        }

        $disk = Storage::disk(config('images.disk'));

        return $disk->exists($path) && $disk->delete($path);
    }

    /**
     * @return array{0: string, 1: bool} [data webp, apakah diperkecil]
     */
    private function encode(string $source, string $realPath, int $maxWidth): array
    {
        $image = @imagecreatefromstring($source);

        if (! $image instanceof GdImage) {
            throw new InvalidArgumentException('Gambar tidak dapat dibaca.');
        }

        try {
            if (! imageistruecolor($image)) {
                imagepalettetotruecolor($image);
            }

            $image = $this->applyExifOrientation($image, $source, $realPath);

            $resized = false;

            if ($maxWidth > 0 && imagesx($image) > $maxWidth) {
                $scaled = imagescale($image, $maxWidth, -1, IMG_BICUBIC);

                if ($scaled instanceof GdImage) {
                    $image = $scaled;
                    $resized = true;
                }
            }

            imagealphablending($image, false);
            imagesavealpha($image, true);

            ob_start();
            $ok = imagewebp($image, null, (int) config('images.quality'));
            $data = (string) ob_get_clean();

            if (! $ok || $data === '') {
                throw new RuntimeException('Gagal mengonversi gambar ke WebP.');
            }

            return [$data, $resized];
        } finally {
            unset($image);
        }
    }

    private function applyExifOrientation(GdImage $image, string $source, string $realPath): GdImage
    {
        if (! function_exists('exif_read_data') || ! $this->isJpeg($source)) {
            return $image;
        }

        $exif = @exif_read_data($realPath);
        $angle = match ($exif['Orientation'] ?? 1) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        return imagerotate($image, $angle, 0) ?: $image;
    }

    private function isJpeg(string $data): bool
    {
        return str_starts_with($data, "\xFF\xD8\xFF");
    }

    private function isWebp(string $data): bool
    {
        return substr($data, 0, 4) === 'RIFF' && substr($data, 8, 4) === 'WEBP';
    }
}
