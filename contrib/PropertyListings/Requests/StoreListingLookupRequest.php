<?php

namespace App\Modules\PropertyListings\Requests;

use App\Modules\PropertyListings\Models\ListingLookup;
use App\Modules\PropertyListings\Services\ListingLookupAdminService;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreListingLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ListingLookup::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sort_order' => $this->input('sort_order', 0),
            'is_active' => filter_var($this->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        $group = $this->resolvedGroup();

        return array_merge([
            'value' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('listing_lookups', 'value')->where('group', $group),
            ],
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], $this->metaRules($group));
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->resolvedGroup() === null) {
                $validator->errors()->add('group', 'Unknown lookup group.');
            }
        });
    }

    public function resolvedGroup(): ?string
    {
        return app(ListingLookupAdminService::class)->groupFromSlug((string) $this->route('group'));
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
}
