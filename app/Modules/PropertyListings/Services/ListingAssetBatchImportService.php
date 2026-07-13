<?php

namespace App\Modules\PropertyListings\Services;

use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingAsset;
use App\Services\ActivityLogger;
use App\Services\Media\MediaUploadService;
use App\Services\Media\MediaUsageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class ListingAssetBatchImportService
{
    public function __construct(
        protected ListingAssetBatchMatcher $matcher,
        protected ListingAssetImageProcessor $imageProcessor,
        protected MediaUploadService $mediaUpload,
        protected MediaUsageService $mediaUsage,
    ) {}

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array{
     *     items: array<int, array<string, mixed>>,
     *     summary: array{valid: int, invalid: int, skipped: int, replace: int}
     * }
     */
    public function preview(array $files, ?string $assetType = null): array
    {
        $items = [];

        foreach ($this->expandFiles($files) as $file) {
            $items[] = $this->previewOne($file, $assetType);
        }

        return [
            'items' => $items,
            'summary' => $this->summarize($items),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function previewOne(UploadedFile $file, ?string $assetType = null): array
    {
        return $this->matcher->match($file, $assetType);
    }

    /**
     * @param  array<int, array{asset_type: string, file: UploadedFile}>  $typedFiles
     * @return array{
     *     items: array<int, array<string, mixed>>,
     *     summary: array{valid: int, invalid: int, skipped: int, replace: int}
     * }
     */
    public function previewTyped(Listing $listing, array $typedFiles): array
    {
        $items = [];

        foreach ($typedFiles as $upload) {
            $file = $upload['file'] ?? null;
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $items[] = $this->matcher->matchTyped($file, $listing, (string) $upload['asset_type']);
        }

        return [
            'items' => $items,
            'summary' => $this->summarize($items),
        ];
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array{
     *     attached: int,
     *     replaced: int,
     *     failed: int,
     *     skipped: int,
     *     errors: array<int, string>
     * }
     */
    public function commit(array $files, int $userId, ?string $assetType = null): array
    {
        $attached = 0;
        $replaced = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];
        $items = [];
        $expandedFiles = $this->expandFiles($files);

        foreach ($expandedFiles as $file) {
            $item = $this->matcher->match($file, $assetType);
            $items[] = [$item, $file];

            if ((bool) ($item['skipped'] ?? false)) {
                $skipped++;

                continue;
            }

            if (! $item['valid']) {
                $errors[] = $item['filename'].': '.implode(' ', $item['errors']);
            }
        }

        if ($errors !== []) {
            return [
                'attached' => 0,
                'replaced' => 0,
                'failed' => count($errors),
                'skipped' => $skipped,
                'errors' => $errors,
            ];
        }

        DB::transaction(function () use ($items, $userId, &$attached, &$replaced, &$failed, &$errors) {
            foreach ($items as [$item, $file]) {
                if ((bool) ($item['skipped'] ?? false)) {
                    continue;
                }

                try {
                    $listing = Listing::query()->findOrFail($item['listing_id']);
                    $outcome = $this->attachMatchedItem($listing, $item, $file, $userId);

                    if ($outcome === 'replaced') {
                        $replaced++;
                    } else {
                        $attached++;
                    }
                } catch (\Throwable $exception) {
                    $failed++;
                    $errors[] = $item['filename'].': '.$exception->getMessage();
                }
            }
        });

        ActivityLogger::log('property-listings', 'batch_assets_imported', new ListingAsset, [
            'attached' => $attached,
            'replaced' => $replaced,
            'failed' => $failed,
            'skipped' => $skipped,
        ]);

        return [
            'attached' => $attached,
            'replaced' => $replaced,
            'failed' => $failed,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * @return array{
     *     filename: string,
     *     status: string,
     *     attached: int,
     *     replaced: int,
     *     failed: int,
     *     skipped: int,
     *     errors: array<int, string>
     * }
     */
    public function commitOne(UploadedFile $file, int $userId, ?string $assetType = null): array
    {
        $item = $this->matcher->match($file, $assetType);
        $filename = (string) ($item['filename'] ?? $file->getClientOriginalName());

        if ((bool) ($item['skipped'] ?? false)) {
            return [
                'filename' => $filename,
                'status' => 'skipped',
                'attached' => 0,
                'replaced' => 0,
                'failed' => 0,
                'skipped' => 1,
                'errors' => [],
            ];
        }

        if (! (bool) ($item['valid'] ?? false)) {
            return [
                'filename' => $filename,
                'status' => 'failed',
                'attached' => 0,
                'replaced' => 0,
                'failed' => 1,
                'skipped' => 0,
                'errors' => [$filename.': '.implode(' ', $item['errors'] ?? [])],
            ];
        }

        try {
            $listing = Listing::query()->findOrFail($item['listing_id']);
            $outcome = $this->attachMatchedItem($listing, $item, $file, $userId);

            return [
                'filename' => $filename,
                'status' => $outcome,
                'attached' => $outcome === 'attached' ? 1 : 0,
                'replaced' => $outcome === 'replaced' ? 1 : 0,
                'failed' => 0,
                'skipped' => 0,
                'errors' => [],
            ];
        } catch (\Throwable $exception) {
            return [
                'filename' => $filename,
                'status' => 'failed',
                'attached' => 0,
                'replaced' => 0,
                'failed' => 1,
                'skipped' => 0,
                'errors' => [$filename.': '.$exception->getMessage()],
            ];
        }
    }

    /**
     * @param  array<int, array{asset_type: string, file: UploadedFile}>  $typedFiles
     * @return array{
     *     attached: int,
     *     replaced: int,
     *     failed: int,
     *     errors: array<int, string>
     * }
     */
    public function commitTyped(Listing $listing, array $typedFiles, int $userId): array
    {
        $attached = 0;
        $replaced = 0;
        $failed = 0;
        $errors = [];

        DB::transaction(function () use ($listing, $typedFiles, $userId, &$attached, &$replaced, &$failed, &$errors) {
            $processedAssetTypes = [];

            foreach ($typedFiles as $upload) {
                $file = $upload['file'] ?? null;
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $assetType = (string) $upload['asset_type'];
                $item = $this->matcher->matchTyped($file, $listing, (string) $assetType);

                if (! $item['valid']) {
                    $failed++;
                    $errors[] = $item['filename'].': '.implode(' ', $item['errors']);

                    continue;
                }

                try {
                    if (in_array($assetType, $processedAssetTypes, true)) {
                        $item['replaces_existing'] = false;
                        $item['existing_asset_id'] = null;
                    }

                    $outcome = $this->attachMatchedItem($listing, $item, $file, $userId);
                    $processedAssetTypes[] = $assetType;

                    if ($outcome === 'replaced') {
                        $replaced++;
                    } else {
                        $attached++;
                    }
                } catch (\Throwable $exception) {
                    $failed++;
                    $errors[] = $item['filename'].': '.$exception->getMessage();
                }
            }
        });

        ActivityLogger::log('property-listings', 'batch_assets_imported', new ListingAsset, [
            'listing_id' => $listing->id,
            'attached' => $attached,
            'replaced' => $replaced,
            'failed' => $failed,
        ]);

        return [
            'attached' => $attached,
            'replaced' => $replaced,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function attachMatchedItem(Listing $listing, array $item, UploadedFile $file, int $userId): string
    {
        $processed = $this->imageProcessor->process($file);
        $media = $this->mediaUpload->upload($processed, [
            'title' => $item['code'].' '.$item['asset_type_label'],
        ], $userId);

        $replaced = false;

        if ($item['replaces_existing'] && $item['existing_asset_id'] !== null) {
            $existing = ListingAsset::query()->find($item['existing_asset_id']);
            if ($existing !== null) {
                $this->mediaUsage->removeModel($existing);
                $existing->delete();
            }
            $replaced = true;
        }

        $asset = $listing->assets()->create([
            'asset_type' => $item['asset_type'],
            'media_id' => $media->id,
            'sort_order' => 0,
        ]);

        $this->mediaUsage->register(
            $media,
            $asset,
            'property-listings',
            'asset_media',
            'Listing Asset',
        );

        $this->mediaUsage->syncRelatedMedia(
            $listing->refresh(),
            'property-listings',
            'asset_media',
            $listing->assets()->pluck('media_id')->all(),
            'Listing Asset',
        );

        return $replaced ? 'replaced' : 'attached';
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{valid: int, invalid: int, skipped: int, replace: int}
     */
    protected function summarize(array $items): array
    {
        return [
            'valid' => collect($items)->where('valid', true)->count(),
            'invalid' => collect($items)
                ->where('valid', false)
                ->reject(fn (array $item) => (bool) ($item['skipped'] ?? false))
                ->count(),
            'skipped' => collect($items)->filter(fn (array $item) => (bool) ($item['skipped'] ?? false))->count(),
            'replace' => collect($items)->where('replaces_existing', true)->where('valid', true)->count(),
        ];
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, UploadedFile>
     */
    protected function expandFiles(array $files): array
    {
        $expanded = [];

        foreach ($files as $file) {
            if ($this->isZip($file)) {
                $expanded = [...$expanded, ...$this->extractZip($file)];

                continue;
            }

            $expanded[] = $file;
        }

        return $expanded;
    }

    protected function isZip(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return $extension === 'zip' || $file->getMimeType() === 'application/zip';
    }

    /**
     * @return array<int, UploadedFile>
     */
    protected function extractZip(UploadedFile $zipFile): array
    {
        $zip = new ZipArchive;
        $files = [];

        if ($zip->open($zipFile->getRealPath()) !== true) {
            return [];
        }

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $stat = $zip->statIndex($index);
            $name = basename((string) ($stat['name'] ?? ''));

            if ($name === '' || str_ends_with((string) $stat['name'], '/')) {
                continue;
            }

            $contents = $zip->getFromIndex($index);
            if ($contents === false) {
                continue;
            }

            $tempPath = tempnam(sys_get_temp_dir(), 'listing-batch-');
            if ($tempPath === false) {
                continue;
            }

            file_put_contents($tempPath, $contents);

            $files[] = new UploadedFile(
                $tempPath,
                $name,
                null,
                null,
                true,
            );
        }

        $zip->close();

        return $files;
    }
}
