<?php

namespace App\Modules\Posts\Controllers;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Modules\Posts\Models\Post;
use App\Modules\Posts\Requests\StorePostRequest;
use App\Modules\Posts\Requests\UpdatePostRequest;
use App\Services\ActivityLogger;
use App\Support\SeoFields;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Post::class);
        $posts = Post::with('author')->latest()->paginate(15);

        return view('posts::posts.index', compact('posts'));
    }

    public function create(): View
    {
        $this->authorize('create', Post::class);

        return view('posts::posts.create', ['statuses' => ContentStatus::cases()]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = Post::create([
            ...$request->safe()->except(array_keys(SeoFields::rules())),
            'author_id' => $request->user()->id,
        ]);
        $post->saveSeo(SeoFields::extract($request->validated()));
        ActivityLogger::log('posts', 'created', $post);

        return redirect()->route('admin.posts.index')->with('success', 'Post created successfully.');
    }

    public function edit(Post $post): View
    {
        $this->authorize('update', $post);
        $post->load('seoMeta');

        return view('posts::posts.edit', ['post' => $post, 'statuses' => ContentStatus::cases()]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->safe()->except(array_keys(SeoFields::rules())));
        $post->saveSeo(SeoFields::extract($request->validated()));
        ActivityLogger::log('posts', 'updated', $post);

        return redirect()->route('admin.posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);
        ActivityLogger::log('posts', 'deleted', $post);
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Post deleted successfully.');
    }

    public function publish(Post $post): RedirectResponse
    {
        $this->authorize('publish', $post);
        $post->update(['status' => ContentStatus::Published, 'published_at' => now()]);
        ActivityLogger::log('posts', 'published', $post);

        return back()->with('success', 'Post published successfully.');
    }

    public function duplicate(Post $post): RedirectResponse
    {
        $this->authorize('create', Post::class);
        $new = $post->replicate(['slug', 'published_at']);
        $new->title = $post->title.' (Copy)';
        $new->slug = $post->slug.'-copy-'.Str::random(4);
        $new->status = ContentStatus::Draft;
        $new->author_id = auth()->id();
        $new->save();
        if ($post->seoMeta) {
            $new->saveSeo($post->seoMeta->only(['seo_title', 'meta_description', 'meta_keywords', 'canonical_url', 'og_title', 'og_description', 'og_image_id', 'robots']));
        }

        return redirect()->route('admin.posts.edit', $new)->with('success', 'Post duplicated.');
    }
}
