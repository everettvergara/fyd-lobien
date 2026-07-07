<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Content\Services\ContentTypeListingRenderService;
use App\Modules\Content\Services\ContentUrlService;
use App\Modules\PageManager\Services\PageManagerService;
use App\Services\Public\PageRenderService;
use Inertia\Inertia;
use Inertia\Response;

class PublicPageController extends Controller
{
    public function __construct(
        protected PageManagerService $pages,
        protected PageRenderService $renderer,
        protected ContentTypeListingRenderService $listingRenderer,
        protected ContentUrlService $contentUrls,
    ) {}

    public function show(?string $path = null): Response
    {
        $normalized = $path === null || $path === '' ? '/' : '/'.ltrim($path, '/');
        $normalized = rtrim($normalized, '/') ?: '/';

        $page = $this->pages->resolvePublishedPage($normalized);

        if ($page === null) {
            $type = $this->contentUrls->resolveTypeListing($normalized);

            if ($type !== null) {
                return Inertia::render('Page/Show', $this->listingRenderer->render($type));
            }

            abort(404);
        }

        return Inertia::render('Page/Show', $this->renderer->render($page));
    }
}
