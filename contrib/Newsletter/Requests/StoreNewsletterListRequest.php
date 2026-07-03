<?php

namespace App\Modules\Newsletter\Requests;

use App\Modules\Newsletter\Models\NewsletterList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreNewsletterListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', NewsletterList::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('newsletter_lists', 'slug')],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'settings.subscribe_label' => ['nullable', 'string', 'max:255'],
            'settings.unsubscribe_label' => ['nullable', 'string', 'max:255'],
            'settings.success_subscribe' => ['nullable', 'string', 'max:500'],
            'settings.success_unsubscribe' => ['nullable', 'string', 'max:500'],
            'settings.placeholder_email' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }

        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
