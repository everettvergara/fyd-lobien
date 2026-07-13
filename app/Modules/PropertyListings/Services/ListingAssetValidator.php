<?php

namespace App\Modules\PropertyListings\Services;

use App\Models\Media;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use App\Modules\PropertyListings\Support\ListingLookupRegistry;
use Illuminate\Http\UploadedFile;

class ListingAssetValidator
{
    public function __construct(
        protected ListingLookupRegistry $registry,
    ) {}

    public function validate(string $assetType, UploadedFile|Media $file): ?string
    {
        if (! $this->registry->hasValue(ListingLookupGroups::IMAGE_TYPE, $assetType)) {
            return 'Invalid asset type selected.';
        }

        $extension = strtolower($this->extensionFor($file));
        $allowed = $this->registry->allowedExtensions(ListingLookupGroups::IMAGE_TYPE, $assetType);

        if ($allowed === []) {
            return 'Asset type has no allowed file extensions configured.';
        }

        if (! in_array($extension, $allowed, true)) {
            $kind = $this->registry->fileKind(ListingLookupGroups::IMAGE_TYPE, $assetType) ?? 'file';

            return sprintf(
                'The %s asset type only accepts %s files.',
                $this->registry->label(ListingLookupGroups::IMAGE_TYPE, $assetType),
                implode(', ', $allowed),
            )." Received .{$extension}.";
        }

        $fileKind = $this->registry->fileKind(ListingLookupGroups::IMAGE_TYPE, $assetType);

        if ($fileKind === 'pdf' && $extension !== 'pdf') {
            return 'PDF asset types only accept PDF files.';
        }

        if ($fileKind === 'image' && $extension === 'pdf') {
            return 'Image asset types do not accept PDF files.';
        }

        return null;
    }

    public function isValid(string $assetType, UploadedFile|Media $file): bool
    {
        return $this->validate($assetType, $file) === null;
    }

    protected function extensionFor(UploadedFile|Media $file): string
    {
        if ($file instanceof Media) {
            return (string) $file->extension;
        }

        return (string) ($file->getClientOriginalExtension() ?: $file->extension());
    }
}
