<?php

namespace App\Modules\PropertyListings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Requests\ListingAssetBatchRequest;
use App\Modules\PropertyListings\Services\ListingAssetBatchImportService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ListingAssetBatchController extends Controller
{
    public function __construct(
        protected ListingAssetBatchImportService $batch,
    ) {}

    public function batchForm(): View
    {
        $this->authorize('batchAssets', Listing::class);

        return view('propertylistings::listings.assets-batch');
    }

    public function batchPreview(ListingAssetBatchRequest $request): View
    {
        $files = $this->resolveUploads($request);
        $batchKey = $this->storeBatchFiles($files);

        return view('propertylistings::listings.assets-batch', [
            'preview' => $this->batch->preview($files),
            'batchKey' => $batchKey,
        ]);
    }

    public function batchCommit(Request $request): RedirectResponse
    {
        $this->authorize('batchAssets', Listing::class);

        $request->validate([
            'batch_key' => ['required', 'string'],
        ]);

        abort_unless($request->input('batch_key') === session('listing_batch.key'), 403);

        $files = $this->restoreBatchFiles();

        $result = $this->batch->commit($files, (int) $request->user()->id);

        $this->clearBatchFiles();

        ActivityLogger::log('listings', 'batch_assets_committed', null, [
            'attached' => $result['attached'] ?? 0,
            'replaced' => $result['replaced'] ?? 0,
            'failed' => $result['failed'] ?? 0,
        ]);

        return redirect()
            ->route('admin.listings.index')
            ->with('success', sprintf(
                'Batch upload complete: %d attached, %d replaced, %d failed.',
                $result['attached'] ?? 0,
                $result['replaced'] ?? 0,
                $result['failed'] ?? 0,
            ));
    }

    public function listingBatchUpload(Listing $listing, ListingAssetBatchRequest $request): RedirectResponse
    {
        $this->authorize('batchAssets', Listing::class);

        $typedFiles = $this->resolveTypedUploads($request);
        $result = $this->batch->commitTyped($listing, $typedFiles, (int) $request->user()->id);

        return redirect()
            ->route('admin.listings.edit', $listing)
            ->withFragment('listing-assets-tab')
            ->with('success', sprintf(
                'Asset upload complete: %d attached, %d replaced, %d failed.',
                $result['attached'] ?? 0,
                $result['replaced'] ?? 0,
                $result['failed'] ?? 0,
            ));
    }

    public function listingBatchPreview(Listing $listing, ListingAssetBatchRequest $request): RedirectResponse
    {
        $this->authorize('batchAssets', Listing::class);

        $typedFiles = $this->resolveTypedUploads($request);
        $batchKey = $this->storeListingBatchFiles($listing, $typedFiles);
        $preview = $this->batch->previewTyped($listing, $typedFiles);

        session([
            "listing_batch.{$listing->id}.key" => $batchKey,
            "listing_batch.{$listing->id}.preview" => $preview,
        ]);

        return redirect()
            ->route('admin.listings.edit', $listing)
            ->withFragment('listing-assets-tab');
    }

    public function listingBatchCommit(Listing $listing, Request $request): RedirectResponse
    {
        $this->authorize('batchAssets', Listing::class);

        $request->validate([
            'batch_key' => ['required', 'string'],
        ]);

        abort_unless(
            $request->input('batch_key') === session("listing_batch.{$listing->id}.key"),
            403,
        );

        $typedFiles = $this->restoreListingBatchFiles($listing);

        $result = $this->batch->commitTyped($listing, $typedFiles, (int) $request->user()->id);

        $this->clearListingBatchFiles($listing);

        return redirect()
            ->route('admin.listings.edit', $listing)
            ->withFragment('listing-assets-tab')
            ->with('success', sprintf(
                'Asset upload complete: %d attached, %d replaced, %d failed.',
                $result['attached'] ?? 0,
                $result['replaced'] ?? 0,
                $result['failed'] ?? 0,
            ));
    }

    /**
     * @return array<int, UploadedFile>
     */
    protected function resolveUploads(ListingAssetBatchRequest $request): array
    {
        if ($request->hasFile('archive')) {
            return [$request->file('archive')];
        }

        return array_values(array_filter($request->file('files') ?? []));
    }

    /**
     * @return array<int, array{asset_type: string, file: UploadedFile}>
     */
    protected function resolveTypedUploads(ListingAssetBatchRequest $request): array
    {
        $uploads = [];

        foreach ($request->file('typed_files') ?? [] as $assetType => $files) {
            foreach ((array) $files as $file) {
                if ($file instanceof UploadedFile) {
                    $uploads[] = [
                        'asset_type' => (string) $assetType,
                        'file' => $file,
                    ];
                }
            }
        }

        return $uploads;
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    protected function storeBatchFiles(array $files): string
    {
        $batchKey = uniqid('listing-batch-', true);
        $stored = [];

        foreach ($files as $file) {
            $path = $file->storeAs('listing-batch-uploads/'.$batchKey, $file->getClientOriginalName());
            $stored[] = [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
            ];
        }

        session([
            'listing_batch.key' => $batchKey,
            'listing_batch.files' => $stored,
        ]);

        return $batchKey;
    }

    /**
     * @param  array<int, array{asset_type: string, file: UploadedFile}>  $typedFiles
     */
    protected function storeListingBatchFiles(Listing $listing, array $typedFiles): string
    {
        $batchKey = uniqid('listing-batch-'.$listing->id.'-', true);
        $stored = [];

        foreach ($typedFiles as $upload) {
            $file = $upload['file'];
            $path = $file->storeAs(
                'listing-batch-uploads/'.$batchKey,
                uniqid('', true).'-'.$file->getClientOriginalName(),
            );
            $stored[] = [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'asset_type' => $upload['asset_type'],
            ];
        }

        session([
            "listing_batch.{$listing->id}.files" => $stored,
        ]);

        return $batchKey;
    }

    /**
     * @return array<int, UploadedFile>
     */
    protected function restoreBatchFiles(): array
    {
        return collect(session('listing_batch.files', []))
            ->map(function (array $meta) {
                $fullPath = Storage::path($meta['path']);

                return new UploadedFile(
                    $fullPath,
                    $meta['name'],
                    null,
                    null,
                    true,
                );
            })
            ->all();
    }

    /**
     * @return array<int, array{asset_type: string, file: UploadedFile}>
     */
    protected function restoreListingBatchFiles(Listing $listing): array
    {
        $typedFiles = [];

        foreach (session("listing_batch.{$listing->id}.files", []) as $meta) {
            $fullPath = Storage::path($meta['path']);
            $typedFiles[] = [
                'asset_type' => (string) $meta['asset_type'],
                'file' => new UploadedFile(
                    $fullPath,
                    $meta['name'],
                    null,
                    null,
                    true,
                ),
            ];
        }

        return $typedFiles;
    }

    protected function clearBatchFiles(): void
    {
        $batchKey = session('listing_batch.key');

        if (is_string($batchKey) && $batchKey !== '') {
            Storage::deleteDirectory('listing-batch-uploads/'.$batchKey);
        }

        session()->forget(['listing_batch.key', 'listing_batch.files']);
    }

    protected function clearListingBatchFiles(Listing $listing): void
    {
        $batchKey = session("listing_batch.{$listing->id}.key");

        if (is_string($batchKey) && $batchKey !== '') {
            Storage::deleteDirectory('listing-batch-uploads/'.$batchKey);
        }

        session()->forget([
            "listing_batch.{$listing->id}.key",
            "listing_batch.{$listing->id}.files",
            "listing_batch.{$listing->id}.preview",
        ]);
    }
}
