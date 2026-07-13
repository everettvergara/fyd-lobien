<?php

namespace App\Modules\PropertyListings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingFee;
use App\Modules\PropertyListings\Models\ListingUnit;
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

    public function importForm(string $type = 'header'): View
    {
        $this->authorize('import', Listing::class);

        return view('propertylistings::uploaders.import', [
            'type' => $this->normalizeType($type),
        ]);
    }

    public function importPreview(ListingImportRequest $request, string $type = 'header'): View
    {
        $type = $this->normalizeType($type);
        $file = $request->file('file');
        $storedPath = $file->store('listing-imports');

        session([
            'listing_import.path' => $storedPath,
            'listing_import.name' => $file->getClientOriginalName(),
            'listing_import.type' => $type,
        ]);

        $preview = $this->import->preview($file, $type);
        foreach ($preview['rows'] as &$row) {
            $row['_action'] = $this->rowExists($type, $row) ? 'update' : 'create';
        }
        unset($row);

        return view('propertylistings::uploaders.import', [
            'type' => $type,
            'preview' => [
                ...$preview,
                'errors' => [
                    ...($preview['batch_errors'] ?? []),
                    ...collect($preview['row_errors'] ?? [])->map(
                        fn (array $errors, int $row) => 'Row '.$row.': '.implode(' ', $errors)
                    )->values()->all(),
                ],
                'warnings' => $preview['warnings'] ?? [],
            ],
            'importKey' => $storedPath,
        ]);
    }

    public function importCommit(Request $request, string $type = 'header'): RedirectResponse
    {
        $this->authorize('import', Listing::class);
        $type = $this->normalizeType($type);

        $request->validate([
            'import_key' => ['required', 'string'],
        ]);

        $storedPath = (string) $request->input('import_key');

        abort_unless($storedPath === session('listing_import.path'), 403);
        abort_unless($type === session('listing_import.type', $type), 403);

        $fullPath = Storage::path($storedPath);
        $file = new UploadedFile(
            $fullPath,
            (string) session('listing_import.name', basename($storedPath)),
            null,
            null,
            true,
        );

        $result = $this->import->commit($file, $type);

        Storage::delete($storedPath);
        session()->forget(['listing_import.path', 'listing_import.name', 'listing_import.type']);

        if (($result['errors'] ?? []) !== [] || ($result['batch_errors'] ?? []) !== []) {
            return redirect()
                ->route('admin.property-uploaders.import', ['type' => $type])
                ->with('error', 'Import failed due to validation errors in the CSV.');
        }

        $redirect = redirect()
            ->route('admin.property-uploaders.index')
            ->with('success', sprintf(
                '%s import complete: %d created, %d updated.',
                $this->typeLabel($type),
                $result['created'] ?? 0,
                $result['updated'] ?? 0,
            ));

        if (($result['warnings'] ?? []) !== []) {
            $redirect->with('warning', implode(' ', $result['warnings']));
        }

        return $redirect;
    }

    public function template(ListingExportService $export, string $type = 'header'): StreamedResponse
    {
        $this->authorize('import', Listing::class);

        return $export->template($this->normalizeType($type));
    }

    protected function normalizeType(string $type): string
    {
        $type = strtolower(trim($type));

        if ($type === 'properties' || $type === 'listings') {
            return 'header';
        }

        abort_unless(in_array($type, ['header', 'units', 'fees'], true), 404);

        return $type;
    }

    protected function typeLabel(string $type): string
    {
        return match ($type) {
            'header' => 'Property header',
            'units' => 'Property units',
            'fees' => 'Property fees',
        };
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function rowExists(string $type, array $row): bool
    {
        return match ($type) {
            'header' => Listing::query()
                ->where('code', trim((string) ($row['code'] ?? '')))
                ->exists(),
            'units' => ListingUnit::query()
                ->join('listings', 'listings.id', '=', 'listing_units.listing_id')
                ->where('listings.code', trim((string) ($row['code'] ?? '')))
                ->where('listing_units.floor', trim((string) ($row['floor'] ?? '')))
                ->where('listing_units.unit', trim((string) ($row['unit'] ?? '')))
                ->exists(),
            'fees' => ListingFee::query()
                ->join('listings', 'listings.id', '=', 'listing_fees.listing_id')
                ->where('listings.code', trim((string) ($row['code'] ?? '')))
                ->where('listing_fees.fee_type', trim((string) ($row['fee_type'] ?? '')))
                ->exists(),
        };
    }
}
