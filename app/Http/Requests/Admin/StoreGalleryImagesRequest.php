<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\IndonesianMessages;
use App\Services\ImageUploader;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreGalleryImagesRequest extends FormRequest
{
    use IndonesianMessages;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:'.config('images.gallery_max_per_upload')],
            'images.*' => ['required', ...ImageUploader::rules()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'images' => 'Foto',
            'images.*' => 'File foto',
        ];
    }
}
