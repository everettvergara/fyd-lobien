<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Content;
use App\Support\PublicContent;
use Inertia\Inertia;
use Inertia\Response;

class ContentController extends Controller
{
    public function show(string $slug): Response
    {
        $reserved = ['blog', 'search', 'admin', 'api'];

        if (in_array($slug, $reserved)) {
            abort(404);
        }

        $content = Content::published()
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Content/Show', [
            'content' => PublicContent::entry($content),
            'banner' => PublicContent::bannerByKey('page-'.$slug),
        ]);
    }
}
