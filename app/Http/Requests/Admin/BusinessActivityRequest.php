<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\IndonesianMessages;
use App\Services\ImageUploader;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BusinessActivityRequest extends FormRequest
{
    use IndonesianMessages;

    protected function prepareForValidation(): void
    {
        $slug = trim((string) $this->input('slug'));

        $this->merge([
            // Kosong = dibuat otomatis dari nama saat diterbitkan.
            'slug' => $slug !== '' ? Str::lower($slug) : null,
            'kbli_code' => filled($this->input('kbli_code')) ? trim((string) $this->input('kbli_code')) : null,
            'sort_order' => $this->filled('sort_order') ? $this->input('sort_order') : 0,
            'is_published' => $this->has('status')
                ? $this->input('status') === 'published'
                : $this->boolean('is_published'),
            'remove_image' => $this->boolean('remove_image'),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_category_id' => ['required', 'integer', Rule::exists('service_categories', 'id')],
            // KBLI selalu string (bukan integer); diterima apa adanya sampai kode resmi tersedia.
            'kbli_code' => ['nullable', 'string', 'max:10', 'regex:/^[A-Za-z0-9.\-]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:150',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                // Alamat hanya perlu unik di dalam kategori yang sama.
                Rule::unique('business_activities', 'slug')
                    ->where('service_category_id', $this->input('service_category_id'))
                    ->ignore($this->route('activity')),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'image' => ['nullable', ...ImageUploader::rules()],
            'remove_image' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:65535'],
            'status' => ['nullable', Rule::in(['published', 'draft'])],
            'is_published' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'service_category_id' => 'Kategori',
            'kbli_code' => 'Kode KBLI',
            'name' => 'Nama aktivitas',
            'slug' => 'Alamat URL',
            'status' => 'Status',
            'short_description' => 'Deskripsi singkat',
            'description' => 'Deskripsi detail',
            'image' => 'Gambar',
            'sort_order' => 'Urutan',
        ];
    }
}
