<?php

namespace App\Modules\PropertyListings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Services\ListingExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListingExportController extends Controller
{
    public function export(Request $request, ListingExportService $export, string $type = 'header'): StreamedResponse
    {
        $this->authorize('export', Listing::class);

        return $export->download($request, $type);
    }
}
