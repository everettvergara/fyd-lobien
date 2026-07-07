<?php

namespace App\Modules\Newsletter\Requests;

use App\Modules\Newsletter\Models\NewsletterList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateNewsletterListRequest extends FormRequest
{
    public function authorize(): bool
    {
        $list = $this->route('newsletter_list');

        return $list instanceof NewsletterList
            && ($this->user()?->can('update', $list) ?? false);
    }

    public function rules(): array
    {
        /** @var NewsletterList $list */
        $list = $this->route('newsletter_list');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('newsletter_lists', 'slug')->ignore($list->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'settings.subscribe_label' => ['nullable', 'string', 'max:255'],
            'settings.unsubscribe_label' => ['nullable', 'string', 'max:255'],
            'settings.success_subscribe' => ['nullable', 'string', 'max:500'],
            'settings.success_unsubscribe' => ['nullable', 'string', 'max:500'],
            'settings.placeholder_email' => ['nullable', 'string', 'max:255'],
            ...$this->booleanSettingRules(),
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }

        $settings = $this->input('settings', []);

        foreach (NewsletterList::booleanSettingKeys() as $key) {
            $settings[$key] = NewsletterList::parseSettingBoolean($this->input("settings.{$key}"));
        }

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'settings' => $settings,
        ]);
    }

    /**
     * @return array<string, list<string>>
     */
    protected function booleanSettingRules(): array
    {
        $rules = [];

        foreach (NewsletterList::booleanSettingKeys() as $key) {
            $rules["settings.{$key}"] = ['sometimes', 'boolean'];
        }

        return $rules;
    }
}
