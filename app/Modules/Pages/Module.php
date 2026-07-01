<?php

namespace App\Modules\Pages;

use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Policies\PagePolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Pages';
    }

    public function policies(): array
    {
        return [
            Page::class => PagePolicy::class,
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Pages', 'admin.pages.index', 'pages.view', 'bi-file-earmark-text', 'Content', sort: 20),
        ];
    }
}
