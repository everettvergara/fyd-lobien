<?php

namespace App\Services;

use App\Modules\Pages\Models\Page;
use App\Modules\Posts\Models\Post;
use App\Support\PublicContent;
use Illuminate\Support\Collection;

class ContentSearchService
{
    public function search(string $query, int $limitPerType = 10): Collection
    {
        $query = trim($query);

        if (strlen($query) < 2) {
            return collect();
        }

        $pages = Page::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('summary', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->limit($limitPerType)
            ->get()
            ->map(fn (Page $page) => array_merge(PublicContent::pageCard($page), ['type' => 'page']));

        $posts = Post::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->limit($limitPerType)
            ->get()
            ->map(fn (Post $post) => array_merge(PublicContent::postCard($post), ['type' => 'post']));

        return $pages->concat($posts)->values();
    }
}
