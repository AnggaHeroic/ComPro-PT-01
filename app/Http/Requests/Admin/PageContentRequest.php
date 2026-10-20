<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\IndonesianMessages;
use App\Support\PageContents;
use Illuminate\Foundation\Http\FormRequest;

class PageContentRequest extends FormRequest
{
    use IndonesianMessages;

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
        $rules = ['content' => ['required', 'array']];

        foreach (PageContents::fields((string) $this->route('page')) as $key => $field) {
            $rules["content.{$key}"] = ['required', 'string', 'max:'.($field['max'] ?? 200)];
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
        }

        return $attributes;
    }
}
