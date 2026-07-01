<?php

namespace App\Modules\Sessions;

use App\Models\DatabaseSession;
use App\Modules\Sessions\Policies\SessionPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Sessions';
    }

    public function policies(): array
    {
        return [
            DatabaseSession::class => SessionPolicy::class,
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Sessions', 'admin.sessions.index', 'sessions.view', 'bi-laptop', 'Administration', sort: 92),
        ];
    }
}
