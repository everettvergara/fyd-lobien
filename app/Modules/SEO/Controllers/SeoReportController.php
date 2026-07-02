<?php

namespace App\Modules\SEO\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SEO\Models\SeoSettings;
use App\Modules\SEO\Services\SeoReportAdminListService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoReportController extends Controller
{
    public function __construct(
        protected SeoReportAdminListService $reportList,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SeoSettings::class);

        return view('seo::seo.report.index', [
            'list' => $this->reportList->result($request),
        ]);
    }
}
