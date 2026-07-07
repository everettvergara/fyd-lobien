<?php

namespace App\Modules\PropertyListings\Requests;

use App\Modules\PropertyListings\Models\PropertySearchBanner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertySearchBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $banner = $this->route('property_search_banner');

        return $banner instanceof PropertySearchBanner
            && ($this->user()?->can('update', $banner) ?? false);
    }

    public function rules(): array
    {
        /** @var PropertySearchBanner $banner */
        $banner = $this->route('property_search_banner');

        return [
            'name' => ['required', 'string', 'max:255'],
            'key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9\-]+$/',
                Rule::unique('property_search_banners', 'key')->ignore($banner->id),
            ],
            'heading' => ['nullable', 'string', 'max:255'],
            'background_image_id' => ['nullable', 'integer', 'exists:media,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
