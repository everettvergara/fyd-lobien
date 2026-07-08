<?php

namespace App\Modules\WebForms\Requests;

use App\Modules\PageManager\Models\Page;
use App\Modules\WebForms\Models\Webform;
use App\Modules\WebForms\Requests\Concerns\ValidatesWebformPagePath;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateWebformRequest extends FormRequest
{
    use ValidatesWebformPagePath;

    public function authorize(): bool
    {
        $webform = $this->route('webform');

        return $webform instanceof Webform
            && ($this->user()?->can('update', $webform) ?? false);
    }

    public function rules(): array
    {
        /** @var Webform $webform */
        $webform = $this->route('webform');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('webforms', 'slug')->ignore($webform->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            '_public_page_path' => $this->publicPagePathRules(),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('name') && ! $this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }

        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);

        $this->mergePublicPagePathForValidation();
    }

    protected function syncedPageIdToIgnore(): ?int
    {
        $webform = $this->route('webform');

        if (! $webform instanceof Webform) {
            return null;
        }

        $path = $webform->public_page_path;

        if (! is_string($path) || $path === '') {
            return null;
        }

        return Page::findByPath($path)?->id;
    }
}
