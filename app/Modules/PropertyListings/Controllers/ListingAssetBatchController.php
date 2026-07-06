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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ListingAssetBatchController extends Controller
{
    public function __construct(
        protected ListingAssetBatchImportService $batch,
    ) {}

    public function batchForm(): View
    {
        $this->authorize('batchAssets', Listing::class);

        return view('propertylistings::uploaders.assets-batch');
    }

    public function batchPreview(ListingAssetBatchRequest $request): View
    {
        $files = $this->resolveUploads($request);
        $assetType = (string) $request->input('asset_type');
        $batchKey = $this->storeBatchFiles($files, $assetType);
        $preview = $this->batch->preview($files, $assetType);

        return view('propertylistings::uploaders.assets-batch', [
            'assetType' => $assetType,
            'preview' => $this->previewPayload($preview),
            'batchKey' => $batchKey,
        ]);
    }

    public function batchStageStart(Request $request): JsonResponse
    {
        $this->authorize('batchAssets', Listing::class);

        $validator = Validator::make($request->all(), [
            'asset_type' => ['required', 'string', 'max:100'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $assetType = trim((string) $request->input('asset_type'));
            if ($assetType !== '' && ! app(\App\Modules\PropertyListings\Support\ListingLookupRegistry::class)
                ->hasValue(\App\Modules\PropertyListings\Support\ListingLookupGroups::IMAGE_TYPE, $assetType)) {
                $validator->errors()->add('asset_type', "Unknown asset type \"{$assetType}\".");
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $this->clearBatchFiles();

        $batchKey = uniqid('listing-batch-', true);
        session([
            'listing_batch.key' => $batchKey,
            'listing_batch.files' => [],
            'listing_batch.asset_type' => (string) $request->input('asset_type'),
        ]);

        return response()->json([
            'batch_key' => $batchKey,
        ]);
    }

    public function batchStageFile(Request $request): JsonResponse
    {
        $this->authorize('batchAssets', Listing::class);

        $validator = Validator::make($request->all(), [
            'batch_key' => ['required', 'string'],
            'file' => ['required', 'file', 'max:51200'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        abort_unless($request->input('batch_key') === session('listing_batch.key'), 403);

        $file = $request->file('file');
        abort_unless($file instanceof UploadedFile, 422);

        $batchKey = (string) session('listing_batch.key');
        $path = $file->storeAs(
            'listing-batch-uploads/'.$batchKey,
            uniqid('', true).'-'.$file->getClientOriginalName(),
        );

        $stored = session('listing_batch.files', []);
        $stored[] = [
            'path' => $path,
            'name' => $file->getClientOriginalName(),
        ];
        session(['listing_batch.files' => $stored]);

        return response()->json([
            'filename' => $file->getClientOriginalName(),
            'staged' => count($stored),
        ]);
    }

    public function batchStageValidate(Request $request): JsonResponse
    {
        $this->authorize('batchAssets', Listing::class);

        $request->validate([
            'batch_key' => ['required', 'string'],
            'index' => ['required', 'integer', 'min:0'],
        ]);

        abort_unless($request->input('batch_key') === session('listing_batch.key'), 403);

        $stored = session('listing_batch.files', []);
        $total = count($stored);
        $index = (int) $request->input('index');

        abort_unless($index < $total, 404);

        $item = $this->batch->previewOne(
            $this->restoreBatchFile($index),
            (string) session('listing_batch.asset_type'),
        );
        $processed = $index + 1;

        return response()->json([
            'filename' => $item['filename'] ?? ($stored[$index]['name'] ?? 'File'),
            'status' => $this->previewStatus($item),
            'message' => $this->previewStatusMessage($item),
            'processed' => $processed,
            'total' => $total,
            'percent' => $total > 0 ? (int) round(($processed / $total) * 100) : 100,
            'next_index' => $processed,
            'done' => $processed >= $total,
        ]);
    }

    public function batchStagePreview(Request $request): View
    {
        $this->authorize('batchAssets', Listing::class);

        $request->validate([
            'batch_key' => ['required', 'string'],
        ]);

        abort_unless($request->input('batch_key') === session('listing_batch.key'), 403);

        $files = $this->restoreBatchFiles();
        $assetType = (string) session('listing_batch.asset_type');
        $preview = $this->batch->preview($files, $assetType);

        return view('propertylistings::uploaders.assets-batch', [
            'assetType' => $assetType,
            'preview' => $this->previewPayload($preview),
            'batchKey' => (string) session('listing_batch.key'),
            'progressCommit' => true,
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
        $assetType = (string) session('listing_batch.asset_type');

        $result = $this->batch->commit($files, (int) $request->user()->id, $assetType);

        $this->clearBatchFiles();

        ActivityLogger::log('listings', 'batch_assets_committed', null, [
            'attached' => $result['attached'] ?? 0,
            'replaced' => $result['replaced'] ?? 0,
            'failed' => $result['failed'] ?? 0,
            'skipped' => $result['skipped'] ?? 0,
        ]);

        return redirect()
            ->route('admin.property-uploaders.index')
            ->with(($result['errors'] ?? []) === [] ? 'success' : 'error', sprintf(
                'Batch upload complete: %d attached, %d replaced, %d skipped, %d failed.%s',
                $result['attached'] ?? 0,
                $result['replaced'] ?? 0,
                $result['skipped'] ?? 0,
                $result['failed'] ?? 0,
                ($result['errors'] ?? []) === [] ? '' : ' No files were committed because validation failed.',
            ));
    }

    public function batchCommitProgress(Request $request): JsonResponse
    {
        $this->authorize('batchAssets', Listing::class);

        $request->validate([
            'batch_key' => ['required', 'string'],
            'index' => ['required', 'integer', 'min:0'],
        ]);

        abort_unless($request->input('batch_key') === session('listing_batch.key'), 403);

        $index = (int) $request->input('index');
        $stored = session('listing_batch.files', []);
        $total = count($stored);

        if ($index === 0) {
            session(['listing_batch.commit_summary' => $this->emptyCommitSummary()]);
        }

        $summary = session('listing_batch.commit_summary', $this->emptyCommitSummary());

        if ($total === 0 || $index >= $total) {
            $this->clearBatchFiles();

            return response()->json([
                'done' => true,
                'processed' => $total,
                'total' => $total,
                'percent' => 100,
                'summary' => $summary,
                'redirect_url' => route('admin.property-uploaders.index'),
            ]);
        }

        $file = $this->restoreBatchFile($index);
        $assetType = (string) session('listing_batch.asset_type');
        $result = $this->batch->commitOne($file, (int) $request->user()->id, $assetType);
        $summary = $this->mergeCommitSummary($summary, $result);
        session(['listing_batch.commit_summary' => $summary]);

        $processed = $index + 1;
        $done = $processed >= $total;

        if ($done) {
            ActivityLogger::log('listings', 'batch_assets_committed', null, [
                'attached' => $summary['attached'],
                'replaced' => $summary['replaced'],
                'failed' => $summary['failed'],
                'skipped' => $summary['skipped'],
            ]);
            $this->clearBatchFiles();
        }

        return response()->json([
            'done' => $done,
            'processed' => $processed,
            'total' => $total,
            'percent' => $total > 0 ? (int) round(($processed / $total) * 100) : 100,
            'current' => $result,
            'summary' => $summary,
            'next_index' => $processed,
            'redirect_url' => route('admin.property-uploaders.index'),
        ]);
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
    protected function storeBatchFiles(array $files, string $assetType): string
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
            'listing_batch.asset_type' => $assetType,
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

    protected function restoreBatchFile(int $index): UploadedFile
    {
        $meta = session('listing_batch.files', [])[$index] ?? null;
        abort_unless(is_array($meta), 404);

        return new UploadedFile(
            Storage::path($meta['path']),
            $meta['name'],
            null,
            null,
            true,
        );
    }

    /**
     * @param  array<string, mixed>  $preview
     * @return array<string, mixed>
     */
    protected function previewPayload(array $preview): array
    {
        return [
            ...$preview,
            'errors' => collect($preview['items'] ?? [])
                ->reject(fn (array $item) => (bool) ($item['valid'] ?? false) || (bool) ($item['skipped'] ?? false))
                ->map(fn (array $item) => ($item['filename'] ?? 'File').': '.implode(' ', $item['errors'] ?? []))
                ->values()
                ->all(),
            'warnings' => collect($preview['items'] ?? [])
                ->filter(fn (array $item) => (bool) ($item['skipped'] ?? false))
                ->map(fn (array $item) => ($item['filename'] ?? 'File').': '.($item['skip_reason'] ?? 'Skipped.'))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function previewStatus(array $item): string
    {
        if ((bool) ($item['skipped'] ?? false)) {
            return 'skip';
        }

        if (! (bool) ($item['valid'] ?? false)) {
            return 'invalid';
        }

        return (bool) ($item['replaces_existing'] ?? false) ? 'replace' : 'valid';
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function previewStatusMessage(array $item): string
    {
        return match ($this->previewStatus($item)) {
            'skip' => (string) ($item['skip_reason'] ?? 'Skipped.'),
            'invalid' => implode(' ', $item['errors'] ?? ['Invalid file.']),
            'replace' => 'Replace existing asset.',
            default => 'Attach new asset.',
        };
    }

    /**
     * @return array{attached: int, replaced: int, skipped: int, failed: int, errors: array<int, string>}
     */
    protected function emptyCommitSummary(): array
    {
        return [
            'attached' => 0,
            'replaced' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => [],
        ];
    }

    /**
     * @param  array{attached: int, replaced: int, skipped: int, failed: int, errors: array<int, string>}  $summary
     * @param  array<string, mixed>  $result
     * @return array{attached: int, replaced: int, skipped: int, failed: int, errors: array<int, string>}
     */
    protected function mergeCommitSummary(array $summary, array $result): array
    {
        $summary['attached'] += (int) ($result['attached'] ?? 0);
        $summary['replaced'] += (int) ($result['replaced'] ?? 0);
        $summary['skipped'] += (int) ($result['skipped'] ?? 0);
        $summary['failed'] += (int) ($result['failed'] ?? 0);
        $summary['errors'] = [
            ...$summary['errors'],
            ...($result['errors'] ?? []),
        ];

        return $summary;
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

        session()->forget([
            'listing_batch.key',
            'listing_batch.files',
            'listing_batch.asset_type',
            'listing_batch.commit_summary',
        ]);
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
