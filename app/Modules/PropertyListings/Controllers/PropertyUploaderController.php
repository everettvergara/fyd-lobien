<?php

namespace App\Modules\PropertyListings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyListings\Models\Listing;
use Illuminate\View\View;

class PropertyUploaderController extends Controller
{
    public function index(): View
    {
        $canImport = request()->user()?->can('import', Listing::class) ?? false;
        $canExport = request()->user()?->can('export', Listing::class) ?? false;
        $canBatchAssets = request()->user()?->can('batchAssets', Listing::class) ?? false;

        abort_unless($canImport || $canExport || $canBatchAssets, 403);

        return view('propertylistings::uploaders.index');
    }
}
