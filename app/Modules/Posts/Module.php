<?php

namespace App\Modules\Posts;

use App\Modules\Posts\Models\Post;
use App\Modules\Posts\Policies\PostPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Posts';
    }

    public function policies(): array
    {
        return [
            Post::class => PostPolicy::class,
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Posts', 'admin.posts.index', 'posts.view', 'bi-journal-text', 'Content', sort: 30),
        ];
    }
}
