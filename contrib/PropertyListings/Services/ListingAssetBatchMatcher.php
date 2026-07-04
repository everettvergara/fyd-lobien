<?php

namespace App\Modules\PropertyListings\Services;

use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingAsset;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use App\Modules\PropertyListings\Support\ListingLookupRegistry;
use Illuminate\Http\UploadedFile;

class ListingAssetBatchMatcher
{
    public function __construct(
        protected ListingLookupRegistry $registry,
        protected ListingAssetValidator $validator,
    ) {}

    /**
     * @return array{
     *     filename: string,
     *     valid: bool,
     *     code: ?string,
     *     asset_type: ?string,
     *     asset_type_label: ?string,
     *     extension: ?string,
     *     listing_id: ?int,
     *     listing_name: ?string,
     *     replaces_existing: bool,
     *     existing_asset_id: ?int,
     *     errors: array<int, string>
     * }
     */
    public function match(UploadedFile $file): array
    {
        $filename = $file->getClientOriginalName();
        $result = [
            'filename' => $filename,
            'valid' => false,
            'code' => null,
            'asset_type' => null,
            'asset_type_label' => null,
            'extension' => null,
            'listing_id' => null,
            'listing_name' => null,
            'replaces_existing' => false,
            'existing_asset_id' => null,
            'errors' => [],
        ];

        if (! str_contains($filename, '__')) {
            $result['errors'][] = 'Filename must match {code}__{asset_type_slug}.{ext}.';

            return $result;
        }

        [$code, $rest] = explode('__', $filename, 2);
        $code = trim($code);
        $rest = trim($rest);

        if ($code === '' || $rest === '') {
            $result['errors'][] = 'Filename must include both listing code and asset type.';

            return $result;
        }

        $extension = strtolower(pathinfo($rest, PATHINFO_EXTENSION));
        $assetTypeSlug = strtolower(pathinfo($rest, PATHINFO_FILENAME));

        $result['code'] = $code;
        $result['extension'] = $extension !== '' ? $extension : null;

        if ($extension === '' || $assetTypeSlug === '') {
            $result['errors'][] = 'Filename must include a file extension.';

            return $result;
        }

        if (! $this->registry->hasValue(ListingLookupGroups::IMAGE_TYPE, $assetTypeSlug)) {
            $result['errors'][] = "Unknown asset type slug \"{$assetTypeSlug}\".";

            return $result;
        }

        $validationError = $this->validator->validate($assetTypeSlug, $file);
        if ($validationError !== null) {
            $result['errors'][] = $validationError;
        }

        $listing = Listing::query()->where('code', $code)->first();
        if ($listing === null) {
            $result['errors'][] = "Listing code \"{$code}\" was not found.";

            return $result;
        }

        $result['asset_type'] = $assetTypeSlug;
        $result['asset_type_label'] = $this->registry->label(ListingLookupGroups::IMAGE_TYPE, $assetTypeSlug);
        $result['listing_id'] = $listing->id;
        $result['listing_name'] = $listing->name;

        $existing = ListingAsset::query()
            ->where('listing_id', $listing->id)
            ->where('asset_type', $assetTypeSlug)
            ->first();

        if ($existing !== null) {
            $result['replaces_existing'] = true;
            $result['existing_asset_id'] = $existing->id;
        }

        $result['valid'] = $result['errors'] === [];

        return $result;
    }

    /**
     * @return array{
     *     filename: string,
     *     valid: bool,
     *     code: ?string,
     *     asset_type: ?string,
     *     asset_type_label: ?string,
     *     extension: ?string,
     *     listing_id: ?int,
     *     listing_name: ?string,
     *     replaces_existing: bool,
     *     existing_asset_id: ?int,
     *     errors: array<int, string>
     * }
     */
    public function matchTyped(UploadedFile $file, Listing $listing, string $assetType): array
    {
        $filename = $file->getClientOriginalName();
        $assetType = strtolower(trim($assetType));
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $result = [
            'filename' => $filename,
            'valid' => false,
            'code' => $listing->code,
            'asset_type' => null,
            'asset_type_label' => null,
            'extension' => $extension !== '' ? $extension : null,
            'listing_id' => $listing->id,
            'listing_name' => $listing->name,
            'replaces_existing' => false,
            'existing_asset_id' => null,
            'errors' => [],
        ];

        if (! $this->registry->hasValue(ListingLookupGroups::IMAGE_TYPE, $assetType)) {
            $result['errors'][] = "Unknown asset type \"{$assetType}\".";

            return $result;
        }

        $validationError = $this->validator->validate($assetType, $file);
        if ($validationError !== null) {
            $result['errors'][] = $validationError;
        }

        $result['asset_type'] = $assetType;
        $result['asset_type_label'] = $this->registry->label(ListingLookupGroups::IMAGE_TYPE, $assetType);

        $existing = ListingAsset::query()
            ->where('listing_id', $listing->id)
            ->where('asset_type', $assetType)
            ->first();

        if ($existing !== null) {
            $result['replaces_existing'] = true;
            $result['existing_asset_id'] = $existing->id;
        }

        $result['valid'] = $result['errors'] === [];

        return $result;
    }
}
