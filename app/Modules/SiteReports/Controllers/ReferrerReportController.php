<?php

namespace App\Modules\SiteReports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SiteReports\Models\SiteVisit;
use App\Modules\SiteReports\Services\ReferrerReportAdminListService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferrerReportController extends Controller
{
    public function __construct(
        protected ReferrerReportAdminListService $referrerReportList,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SiteVisit::class);

        return view('sitereports::referrers.index', [
            'list' => $this->referrerReportList->result($request),
            'summary' => $this->referrerReportList->summary($request),
        ]);
    }
}
