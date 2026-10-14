<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\IndonesianMessages;
use App\Services\ImageUploader;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGalleryImageRequest extends FormRequest
{
    use IndonesianMessages;

    protected function prepareForValidation(): void
    {
        // Setiap foto punya error bag sendiri agar pesan tidak muncul di kartu lain.
        $this->errorBag = 'image'.$this->route('image')->getKey();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'caption' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', ...ImageUploader::rules()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'caption' => 'Caption',
            'image' => 'Foto pengganti',
        ];
    }
}
