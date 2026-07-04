<?php

namespace App\Modules\PropertyListings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingRemark;
use App\Modules\PropertyListings\Requests\StoreListingRemarkRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ListingRemarkController extends Controller
{
    public function index(Listing $listing): View|JsonResponse
    {
        $this->authorize('view', $listing);

        $remarks = $listing->remarks()
            ->with(['user', 'unit'])
            ->orderByDesc('remarked_at')
            ->orderByDesc('id')
            ->get();

        if (request()->expectsJson()) {
            return response()->json(['remarks' => $remarks]);
        }

        return view('propertylistings::listings.remarks.index', [
            'listing' => $listing,
            'remarks' => $remarks,
        ]);
    }

    public function store(StoreListingRemarkRequest $request, Listing $listing): RedirectResponse|JsonResponse
    {
        $remark = ListingRemark::create([
            'listing_id' => $listing->id,
            'listing_unit_id' => $request->validated('listing_unit_id'),
            'user_id' => $request->user()->id,
            'comment' => $request->validated('comment'),
            'remarked_at' => now(),
        ]);

        $remark->load(['user', 'unit']);

        ActivityLogger::log('listings', 'remark_created', $remark);

        if ($request->expectsJson()) {
            return response()->json([
                'remark' => $remark,
                'message' => 'Remark added.',
            ], 201);
        }

        return redirect()
            ->route('admin.listings.edit', $listing)
            ->with('success', 'Remark added.');
    }
}
