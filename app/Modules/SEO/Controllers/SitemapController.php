<?php

namespace App\Modules\SEO\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SEO\Services\RobotsTxtService;
use App\Modules\SEO\Services\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(
        protected SitemapService $sitemap,
    ) {}

    public function index(): Response
    {
        $xml = $this->sitemap->render();

        if ($xml === null) {
            abort(404);
        }

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
