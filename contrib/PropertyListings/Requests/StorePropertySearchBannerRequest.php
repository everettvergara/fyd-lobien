<?php

namespace App\Modules\PropertyListings\Requests;

use App\Modules\PropertyListings\Models\PropertySearchBanner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePropertySearchBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', PropertySearchBanner::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/', Rule::unique('property_search_banners', 'key')],
            'heading' => ['nullable', 'string', 'max:255'],
            'background_image_id' => ['nullable', 'integer', 'exists:media,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $key = trim((string) $this->input('key', ''));

        if ($key === '' && filled($this->input('name'))) {
            $key = Str::slug((string) $this->input('name'));
        }

        $this->merge([
            'key' => $key,
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
