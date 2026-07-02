<?php

namespace App\Modules\Posts\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Posts\Models\Post;
use App\Modules\Posts\Requests\StorePostRequest;
use App\Modules\Posts\Requests\UpdatePostRequest;
use App\Modules\Posts\Services\PostService;
use App\Services\PublishingService;
use App\Support\OwnContentAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(
        protected PostService $posts,
        protected PublishingService $publishing,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Post::class);

        $query = Post::with('author')->latest();

        if (OwnContentAccess::managesOwnContentOnly(auth()->user())) {
            $query->where('author_id', auth()->id());
        }

        $posts = $query->paginate(15);

        return view('posts::posts.index', compact('posts'));
    }

    public function create(): View
    {
        $this->authorize('create', Post::class);

        return view('posts::posts.create', ['statuses' => \App\Enums\ContentStatus::cases()]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $this->posts->create($request->validated(), $request->user()->id);

        return redirect()->route('admin.posts.index')->with('success', 'Post created successfully.');
    }

    public function edit(Post $post): View
    {
        $this->authorize('update', $post);
        $post->load('seoMeta.ogImage');

        return view('posts::posts.edit', ['post' => $post, 'statuses' => \App\Enums\ContentStatus::cases()]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $this->posts->update($post, $request->validated());

        return redirect()->route('admin.posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);
        $this->posts->delete($post);

        return redirect()->route('admin.posts.index')->with('success', 'Post deleted successfully.');
    }

    public function publish(Post $post): RedirectResponse
    {
        $this->authorize('publish', $post);
        $this->publishing->publish($post, 'posts');

        return back()->with('success', 'Post published successfully.');
    }

    public function duplicate(Post $post): RedirectResponse
    {
        $this->authorize('create', Post::class);
        $post->load('seoMeta');

        $newPost = $this->publishing->duplicate($post, 'posts', [
            'title' => $post->title.' (Copy)',
            'slug' => $this->publishing->generateCopySlug($post->slug),
            'author_id' => auth()->id(),
        ]);

        return redirect()->route('admin.posts.edit', $newPost)->with('success', 'Post duplicated.');
    }
}
