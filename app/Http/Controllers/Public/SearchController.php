<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Models\Page;
use App\Modules\Posts\Models\Post;
use App\Support\PublicContent;
use App\Support\PublicSeo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request): Response
    {
        $query = trim($request->get('q', ''));

        $pages = collect();
        $posts = collect();

        if (strlen($query) >= 2) {
            $pages = Page::published()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('summary', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                })
                ->limit(10)
                ->get()
                ->map(fn ($p) => array_merge(PublicContent::pageCard($p), ['type' => 'page']));

            $posts = Post::published()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                })
                ->limit(10)
                ->get()
                ->map(fn ($p) => array_merge(PublicContent::postCard($p), ['type' => 'post']));
        }

        return Inertia::render('Search', [
            'query' => $query,
            'results' => $pages->concat($posts)->values()->all(),
            'seo' => PublicSeo::defaults('Search'),
        ]);
    }
}
