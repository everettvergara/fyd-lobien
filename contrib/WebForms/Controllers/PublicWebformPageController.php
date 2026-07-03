<?php

namespace App\Modules\WebForms\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\WebForms\Services\WebformPublicService;
use App\Support\PublicSeo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicWebformPageController extends Controller
{
    public function __construct(
        protected WebformPublicService $publicService,
    ) {}

    public function show(Request $request, string $slug): Response
    {
        $webform = $this->publicService->findActiveBySlug($slug);

        if ($webform === null) {
            abort(404);
        }

        return Inertia::render('Webforms/Show', [
            'webform' => $this->publicService->toPublicDto($webform),
            'seo' => PublicSeo::defaults($webform->name),
        ]);
    }
}
