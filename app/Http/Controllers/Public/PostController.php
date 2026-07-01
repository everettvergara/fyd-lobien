<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Posts\Models\Post;
use App\Support\PublicContent;
use App\Support\PublicSeo;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(): Response
    {
        $posts = Post::published()
            ->with('featuredImage')
            ->latest('published_at')
            ->paginate(9)
            ->through(fn ($post) => PublicContent::postCard($post));

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
            'seo' => PublicSeo::defaults('Blog'),
        ]);
    }

    public function show(string $slug): Response
    {
        $post = Post::published()
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Blog/Show', [
            'post' => PublicContent::post($post),
        ]);
    }
}
