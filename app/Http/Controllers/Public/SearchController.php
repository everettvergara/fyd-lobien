<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\SearchRequest;
use App\Services\ContentSearchService;
use App\Support\PublicSeo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __construct(
        protected ContentSearchService $search,
    ) {}

    public function index(Request $request): Response
    {
        $query = trim($request->get('q', ''));

        return Inertia::render('Search', [
            'query' => $query,
            'results' => $this->search->search($query)->all(),
            'seo' => PublicSeo::defaults('Search'),
        ]);
    }

    public function store(SearchRequest $request): RedirectResponse
    {
        return redirect()->route('search', [
            'q' => trim($request->validated('q')),
        ]);
    }
}
