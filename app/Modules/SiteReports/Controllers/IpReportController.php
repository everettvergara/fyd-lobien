<?php

namespace App\Modules\SiteReports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SiteReports\Models\SiteVisit;
use App\Modules\SiteReports\Services\IpReportAdminListService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IpReportController extends Controller
{
    public function __construct(
        protected IpReportAdminListService $ipReportList,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SiteVisit::class);

        return view('sitereports::ips.index', [
            'list' => $this->ipReportList->result($request),
        ]);
    }
}
