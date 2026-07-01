<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Models\Page;
use App\Modules\Posts\Models\Post;
use App\Support\PublicContent;
use App\Support\PublicSeo;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $hero = PublicContent::heroBanner();
        $slider = PublicContent::sliderBanners();

        $latestPosts = Post::published()
            ->with('featuredImage')
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(fn ($p) => PublicContent::postCard($p));

        $featuredPages = Page::published()
            ->with('featuredImage')
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(fn ($p) => PublicContent::pageCard($p));

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
            'slider' => $slider,
            'latestPosts' => $latestPosts,
            'featuredPages' => $featuredPages,
            'seo' => PublicSeo::defaults(),
        ]);
    }
}
