<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\Content;
use App\Support\PublicContent;
use App\Support\PublicSeo;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $hero = PublicContent::bannerByKey('homepage-hero');
        $sliderBanner = PublicContent::bannerByKey('homepage-slider');

        $latestArticles = Content::published()
            ->where('content_type', 'article')
            ->with('featuredImage')
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(fn ($content) => PublicContent::contentCard($content));

        $featuredContent = Content::published()
            ->where('content_type', 'page')
            ->with('featuredImage')
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(fn ($content) => PublicContent::contentCard($content));

        return Inertia::render('Home', [
            'hero' => $hero ?? [
                'title' => 'Welcome',
                'subtitle' => '',
                'description' => 'Your professional corporate website.',
                'buttonText' => 'Learn More',
                'buttonUrl' => '#about',
                'desktopImage' => null,
                'mobileImage' => null,
            ],
            'sliderBanner' => $sliderBanner,
            'latestArticles' => $latestArticles,
            'featuredContent' => $featuredContent,
            'seo' => PublicSeo::defaults(),
        ]);
    }
}
