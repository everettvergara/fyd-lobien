<?php

namespace App\Modules\PropertyListings\Requests;

use App\Modules\PropertyListings\Models\ListingLookup;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateListingLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lookup = $this->route('listing_lookup');

        return $lookup instanceof ListingLookup
            ? ($this->user()?->can('update', $lookup) ?? false)
            : false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    public function rules(): array
    {
        /** @var ListingLookup $lookup */
        $lookup = $this->route('listing_lookup');

        return array_merge([
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], $this->metaRules($lookup->group), $this->propertyTypeProfileRules($lookup->group));
    }

    /**
     * @return array<string, mixed>
     */
    protected function metaRules(string $group): array
    {
        if (! ListingLookupGroups::usesFileKind($group)) {
            return [];
        }

        return [
            'meta' => ['nullable', 'array'],
            'meta.file_kind' => ['nullable', 'string', Rule::in(['image', 'pdf'])],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function propertyTypeProfileRules(string $group): array
    {
        if (! ListingLookupGroups::usesPropertyTypeProfile($group)) {
            return [];
        }

        return [
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'image_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
