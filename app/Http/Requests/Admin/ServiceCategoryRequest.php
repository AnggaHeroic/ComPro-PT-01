<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\IndonesianMessages;
use App\Services\ImageUploader;
use App\Support\CategoryIcons;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceCategoryRequest extends FormRequest
{
    use IndonesianMessages;

    protected function prepareForValidation(): void
    {
        $slug = trim((string) $this->input('slug'));

        $this->merge([
            'slug' => Str::lower($slug !== '' ? $slug : Str::slug((string) $this->input('name'))),
            'sort_order' => $this->filled('sort_order') ? $this->input('sort_order') : 0,
            'is_published' => $this->boolean('is_published'),
            'remove_image' => $this->boolean('remove_image'),
            'remove_icon_image' => $this->boolean('remove_icon_image'),
            'icon' => $this->filled('icon') ? $this->input('icon') : null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required', 'string', 'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('service_categories', 'slug')->ignore($this->route('category')),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', ...ImageUploader::rules()],
            'remove_image' => ['boolean'],
            // Ikon: pilih dari whitelist, atau unggah gambar kustom (diutamakan bila ada).
            'icon' => ['nullable', 'string', Rule::in(CategoryIcons::keys())],
            'icon_image' => ['nullable', ...ImageUploader::rules()],
            'remove_icon_image' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:65535'],
            'is_published' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama kategori',
            'slug' => 'Slug',
            'description' => 'Deskripsi',
            'image' => 'Gambar',
            'icon' => 'Ikon',
            'icon_image' => 'Ikon kustom',
            'sort_order' => 'Urutan',
        ];
    }
}
