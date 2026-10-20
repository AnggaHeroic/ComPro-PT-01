<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\IndonesianMessages;
use App\Services\ImageUploader;
use App\Support\PageContents;
use Illuminate\Foundation\Http\FormRequest;

class PageContentRequest extends FormRequest
{
    use IndonesianMessages {
        messages as baseMessages;
    }

    protected function prepareForValidation(): void
    {
        $content = $this->input('content');

        if (! is_array($content)) {
            return;
        }

        // Baris daftar yang dibiarkan kosong dibuang begitu saja.
        foreach (PageContents::fields((string) $this->route('page')) as $key => $field) {
            if (($field['type'] ?? 'text') === 'list' && is_array($content[$key] ?? null)) {
                $content[$key] = array_values(array_filter(
                    array_map(fn ($item) => is_string($item) ? trim($item) : '', $content[$key]),
                    fn (string $item) => $item !== '',
                ));
            }
        }

        $this->merge(['content' => $content]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->baseMessages(),
            'min.array' => ':attribute minimal :min butir.',
            'max.array' => ':attribute maksimal :max butir.',
        ];
    }

    public function authorize(): bool
    {
        return PageContents::has((string) $this->route('page'));
    }

    protected function failedAuthorization(): void
    {
        abort(404);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $rules = ['content' => ['nullable', 'array']];

        foreach (PageContents::fields((string) $this->route('page')) as $key => $field) {
            if (($field['type'] ?? 'text') === 'image') {
                $rules["image_{$key}"] = ['nullable', ...ImageUploader::rules()];

                continue;
            }

            if (($field['type'] ?? 'text') === 'list') {
                $rules["content.{$key}"] = ['required', 'array', 'min:'.($field['min_items'] ?? 1), 'max:'.($field['max_items'] ?? 10)];
                $rules["content.{$key}.*"] = ['required', 'string', 'max:'.($field['max'] ?? 300)];

                continue;
            }

            $rules["content.{$key}"] = [
                ($field['optional'] ?? false) ? 'nullable' : 'required',
                'string',
                'max:'.($field['max'] ?? 200),
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach (PageContents::fields((string) $this->route('page')) as $key => $field) {
            $attributes["content.{$key}"] = $field['label'];
            $attributes["content.{$key}.*"] = $field['item_label'] ?? $field['label'];
            $attributes["image_{$key}"] = $field['label'];
        }

        return $attributes;
    }
}
