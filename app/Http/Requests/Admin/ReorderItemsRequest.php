<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\IndonesianMessages;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReorderItemsRequest extends FormRequest
{
    use IndonesianMessages;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer', 'distinct'],
            // Hanya dipakai untuk aktivitas: urutan berlaku di dalam satu kategori.
            'category' => ['nullable', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'order' => 'Urutan',
            'order.*' => 'Urutan',
            'category' => 'Kategori',
        ];
    }
}
