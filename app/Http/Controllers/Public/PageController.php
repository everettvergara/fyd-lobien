<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Models\Page;
use App\Support\PublicContent;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function show(string $slug): Response
    {
        $reserved = ['blog', 'search', 'admin', 'api'];

        if (in_array($slug, $reserved)) {
            abort(404);
        }

        $page = Page::published()
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Page', [
            'page' => PublicContent::page($page),
        ]);
    }
}
