<?php

namespace App\Modules\SiteReports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SiteReports\Models\SiteVisit;
use App\Modules\SiteReports\Services\PageReportAdminListService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageReportController extends Controller
{
    public function __construct(
        protected PageReportAdminListService $pageReportList,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SiteVisit::class);

        return view('sitereports::pages.index', [
            'list' => $this->pageReportList->result($request),
        ]);
    }
}
