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
            if (($field['type'] ?? 'text') === 'cards' && is_array($content[$key] ?? null)) {
                $content[$key] = array_values(array_filter(
                    array_map(fn ($item) => is_array($item)
                        ? ['title' => trim((string) ($item['title'] ?? '')), 'text' => trim((string) ($item['text'] ?? ''))]
                        : ['title' => '', 'text' => ''], $content[$key]),
                    fn (array $item) => $item['title'] !== '' || $item['text'] !== '',
                ));
            }

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

            if (($field['type'] ?? 'text') === 'cards') {
                $rules["content.{$key}"] = ['required', 'array', 'min:'.($field['min_items'] ?? 1), 'max:'.($field['max_items'] ?? 10)];
                $rules["content.{$key}.*.title"] = ['required', 'string', 'max:'.($field['max_title'] ?? 60)];
                $rules["content.{$key}.*.text"] = ['required', 'string', 'max:'.($field['max_text'] ?? 300)];

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
                ...($field['rules'] ?? []),
                ...(($field['type'] ?? 'text') === 'title' ? [function (string $attribute, mixed $value, \Closure $fail) {
                    // Tanda * untuk mewarnai harus berpasangan.
                    if (substr_count((string) $value, '*') % 2 !== 0) {
                        $fail('Tanda * pada :attribute harus berpasangan (awal dan akhir kata yang diwarnai).');
                    }
                }] : []),
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
            $attributes["content.{$key}.*.title"] = 'Judul '.mb_strtolower($field['item_label'] ?? 'butir');
            $attributes["content.{$key}.*.text"] = 'Uraian '.mb_strtolower($field['item_label'] ?? 'butir');
            $attributes["image_{$key}"] = $field['label'];
        }

        return $attributes;
    }
}
