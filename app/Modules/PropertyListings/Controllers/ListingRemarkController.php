<?php

namespace App\Modules\PropertyListings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingRemark;
use App\Modules\PropertyListings\Requests\StoreListingRemarkRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ListingRemarkController extends Controller
{
    public const REMARKS_PER_PAGE = 8;

    public function index(Listing $listing): View|JsonResponse
    {
        $this->authorize('view', $listing);

        $remarks = $this->paginatedRemarks($listing);

        if (request()->expectsJson()) {
            return response()->json([
                'remarks' => $remarks->items(),
                'pagination' => [
                    'current_page' => $remarks->currentPage(),
                    'last_page' => $remarks->lastPage(),
                    'per_page' => $remarks->perPage(),
                    'total' => $remarks->total(),
                ],
            ]);
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

    public function destroy(Listing $listing, ListingRemark $remark): RedirectResponse
    {
        abort_unless($remark->listing_id === $listing->id, 404);

        $this->authorize('update', $listing);

        ActivityLogger::log('listings', 'remark_deleted', $remark);

        $remark->delete();

        $params = array_filter([
            'remarks_unit' => request()->query('remarks_unit'),
            'remarks_page' => request()->query('remarks_page'),
        ]);

        $url = route('admin.listings.edit', $listing);
        if ($params !== []) {
            $url .= '?'.http_build_query($params);
        }

        return redirect($url)->with('success', 'Remark deleted.');
    }

    public function paginatedRemarks(Listing $listing): LengthAwarePaginator
    {
        $unitFilter = request()->query('remarks_unit');

        return $listing->remarks()
            ->with(['user.avatar', 'unit'])
            ->when($unitFilter, fn ($query) => $query->where('listing_unit_id', $unitFilter))
            ->orderByDesc('remarked_at')
            ->orderByDesc('id')
            ->paginate(self::REMARKS_PER_PAGE, ['*'], 'remarks_page')
            ->withQueryString();
    }
}
