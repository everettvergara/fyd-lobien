<?php

namespace App\Modules\PropertyListings\Requests\Concerns;

use App\Models\Media;
use App\Modules\PropertyListings\Services\ListingAssetValidator;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use App\Modules\PropertyListings\Support\ListingLookupRegistry;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesListingFields
{
    /**
     * @return array<string, mixed>
     */
    protected function listingFieldRules(): array
    {
        $registry = app(ListingLookupRegistry::class);

        return [
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'brgy' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'office_rental_rate' => ['nullable', 'numeric', 'min:0'],
            'total_area_size' => ['nullable', 'numeric', 'min:0'],
            'unit_market_size' => ['nullable', 'numeric', 'min:0'],
            'retail_market_rate' => ['nullable', 'numeric', 'min:0'],
            'completion_status' => ['nullable', 'string', 'max:100', Rule::in($registry->values(ListingLookupGroups::COMPLETION_STATUS))],
            'published_to_public' => ['nullable', 'boolean'],

            'spec' => ['nullable', 'array'],
            'spec.developer' => ['nullable', 'string', 'max:255'],
            'spec.grade' => ['nullable', 'string', 'max:100', Rule::in($registry->values(ListingLookupGroups::GRADE))],
            'spec.completion_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'spec.completion_qtr' => ['nullable', 'string', 'max:50'],
            'spec.no_of_floors' => ['nullable', 'string', 'max:50'],
            'spec.no_of_basement' => ['nullable', 'string', 'max:50'],
            'spec.density_ratio' => ['nullable', 'string', 'max:50'],
            'spec.parking_allocation' => ['nullable', 'string', 'max:255'],
            'spec.floor_to_ceiling_height' => ['nullable', 'string', 'max:50'],
            'spec.gross_leasable_area' => ['nullable', 'numeric', 'min:0'],
            'spec.typical_floor_area' => ['nullable', 'numeric', 'min:0'],
            'spec.typical_retail_floor_area' => ['nullable', 'numeric', 'min:0'],
            'spec.floor_efficiency' => ['nullable', 'string', 'max:255'],

            'building_service' => ['nullable', 'array'],
            'building_service.operating_hours' => ['nullable', 'string', 'max:255'],
            'building_service.ac_system' => ['nullable', 'string', 'max:255'],
            'building_service.no_of_lifts_passenger' => ['nullable', 'string', 'max:255'],
            'building_service.no_of_lifts_service' => ['nullable', 'string', 'max:255'],
            'building_service.telco' => ['nullable', 'string', 'max:255'],
            'building_service.backup_power' => ['nullable', 'string', 'max:255'],

            'other_info' => ['nullable', 'array'],
            'other_info.peza_accreditation' => ['nullable', 'string', 'max:100', Rule::in($registry->values(ListingLookupGroups::PEZA_ACCREDITATION))],
            'other_info.sustainability' => ['nullable', 'string'],
            'other_info.other_info_visible' => ['nullable', 'boolean'],

            'units' => ['nullable', 'array'],
            'units.*.id' => ['nullable', 'integer'],
            'units.*.floor' => ['nullable', 'string', 'max:50'],
            'units.*.unit' => ['nullable', 'string', 'max:50'],
            'units.*.area_size' => ['nullable', 'numeric', 'min:0'],
            'units.*.rental' => ['nullable', 'numeric', 'min:0'],
            'units.*.handover_condition' => ['nullable', 'string', 'max:100', Rule::in($registry->values(ListingLookupGroups::HANDOVER_CONDITION))],
            'units.*.availability' => ['nullable', 'string', 'max:100', Rule::in($registry->values(ListingLookupGroups::AVAILABILITY))],
            'units.*.bedrooms' => ['nullable', 'string', 'max:100', Rule::in($registry->values(ListingLookupGroups::BEDROOMS))],
            'units.*.selling_price' => ['nullable', 'numeric', 'min:0'],
            'units.*.property_type' => ['nullable', 'string', 'max:100', Rule::in($registry->values(ListingLookupGroups::PROPERTY_TYPE))],
            'units.*.for_lease' => ['nullable', 'boolean'],
            'units.*.for_sale' => ['nullable', 'boolean'],
            'units.*.last_remarks' => ['nullable', 'string'],
            'units.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'fees' => ['nullable', 'array'],
            'fees.*.id' => ['nullable', 'integer'],
            'fees.*.fee_type' => ['required_with:fees.*.fee', 'string', 'max:100', Rule::in($registry->values(ListingLookupGroups::FEE_TYPE))],
            'fees.*.fee' => ['nullable', 'numeric', 'min:0'],
            'fees.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'assets' => ['nullable', 'array'],
            'assets.*.id' => ['nullable', 'integer'],
            'assets.*.asset_type' => ['required_with:assets.*.media_id', 'string', 'max:100', Rule::in($registry->values(ListingLookupGroups::IMAGE_TYPE))],
            'assets.*.media_id' => ['nullable', 'integer', 'exists:media,id'],
            'assets.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareListingFieldsForValidation(): void
    {
        $this->merge([
            'office_rental_rate' => $this->filled('office_rental_rate') ? $this->input('office_rental_rate') : null,
            'total_area_size' => $this->filled('total_area_size') ? $this->input('total_area_size') : null,
            'unit_market_size' => $this->filled('unit_market_size') ? $this->input('unit_market_size') : null,
            'retail_market_rate' => $this->filled('retail_market_rate') ? $this->input('retail_market_rate') : null,
            'completion_status' => $this->input('completion_status') ?: null,
            'published_to_public' => filter_var(
                $this->input('published_to_public', false),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE,
            ) ?? false,
        ]);

        if ($this->has('other_info.other_info_visible')) {
            $this->merge([
                'other_info' => array_merge($this->input('other_info', []), [
                    'other_info_visible' => filter_var(
                        $this->input('other_info.other_info_visible'),
                        FILTER_VALIDATE_BOOLEAN,
                        FILTER_NULL_ON_FAILURE,
                    ) ?? true,
                ]),
            ]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateAssetMediaExtensions($validator);
        });
    }

    protected function validateAssetMediaExtensions(Validator $validator): void
    {
        $assets = $this->input('assets', []);
        if (! is_array($assets)) {
            return;
        }

        $assetValidator = app(ListingAssetValidator::class);

        foreach ($assets as $index => $asset) {
            if (! is_array($asset)) {
                continue;
            }

            $assetType = $asset['asset_type'] ?? null;
            $mediaId = $asset['media_id'] ?? null;

            if (! $assetType || ! $mediaId) {
                continue;
            }

            $media = Media::query()->find($mediaId);
            if ($media === null) {
                continue;
            }

            $error = $assetValidator->validate((string) $assetType, $media);
            if ($error !== null) {
                $validator->errors()->add("assets.{$index}.media_id", $error);
            }
        }
    }
}
