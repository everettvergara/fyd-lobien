<?php

namespace App\Modules\PropertyListings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Requests\ListingImportRequest;
use App\Modules\PropertyListings\Services\ListingExportService;
use App\Modules\PropertyListings\Services\ListingImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListingImportController extends Controller
{
    public function __construct(
        protected ListingImportService $import,
    ) {}

    public function importForm(): View
    {
        $this->authorize('import', Listing::class);

        return view('propertylistings::listings.import');
    }

    public function importPreview(ListingImportRequest $request): View
    {
        $file = $request->file('file');
        $storedPath = $file->store('listing-imports');

        session([
            'listing_import.path' => $storedPath,
            'listing_import.name' => $file->getClientOriginalName(),
        ]);

        $preview = $this->import->preview($file);
        $existingCodes = Listing::query()
            ->whereIn('code', collect($preview['rows'])->pluck('code')->filter()->unique()->all())
            ->pluck('code')
            ->all();

        foreach ($preview['rows'] as &$row) {
            $row['_action'] = in_array($row['code'] ?? '', $existingCodes, true) ? 'update' : 'create';
        }
        unset($row);

        return view('propertylistings::listings.import', [
            'preview' => [
                ...$preview,
                'errors' => collect($preview['row_errors'] ?? [])->flatten()->values()->all(),
            ],
            'importKey' => $storedPath,
        ]);
    }

    public function importCommit(Request $request): RedirectResponse
    {
        $this->authorize('import', Listing::class);

        $request->validate([
            'import_key' => ['required', 'string'],
        ]);

        $storedPath = (string) $request->input('import_key');

        abort_unless($storedPath === session('listing_import.path'), 403);

        $fullPath = Storage::path($storedPath);
        $file = new UploadedFile(
            $fullPath,
            (string) session('listing_import.name', basename($storedPath)),
            null,
            null,
            true,
        );

        $result = $this->import->commit($file);

        Storage::delete($storedPath);
        session()->forget(['listing_import.path', 'listing_import.name']);

        if (($result['errors'] ?? []) !== []) {
            return redirect()
                ->route('admin.listings.import')
                ->with('error', 'Import failed due to validation errors in the CSV.');
        }

        return redirect()
            ->route('admin.listings.index')
            ->with('success', sprintf(
                'Import complete: %d created, %d updated.',
                $result['created'] ?? 0,
                $result['updated'] ?? 0,
            ));
    }

    public function template(ListingExportService $export): StreamedResponse
    {
        $this->authorize('import', Listing::class);

        return $export->template();
    }
}
