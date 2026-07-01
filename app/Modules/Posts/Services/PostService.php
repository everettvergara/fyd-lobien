<?php

namespace App\Modules\Posts\Services;

use App\Modules\Posts\Models\Post;
use App\Modules\SEO\Services\SeoService;
use App\Services\ActivityLogger;

class PostService
{
    public function __construct(
        protected SeoService $seo,
    ) {}

    public function create(array $validated, int $authorId): Post
    {
        $post = Post::create([
            ...collect($validated)->except($this->seo->fieldKeys())->all(),
            'author_id' => $authorId,
        ]);
        $post->saveSeo($this->seo->extract($validated));
        ActivityLogger::log('posts', 'created', $post);

        return $post;
    }

    public function update(Post $post, array $validated): Post
    {
        $post->update(collect($validated)->except($this->seo->fieldKeys())->all());
        $post->saveSeo($this->seo->extract($validated));
        ActivityLogger::log('posts', 'updated', $post);

        return $post;
    }

    public function delete(Post $post): void
    {
        ActivityLogger::log('posts', 'deleted', $post);
        $post->delete();
    }
}
