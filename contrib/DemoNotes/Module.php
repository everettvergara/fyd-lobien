<?php

namespace App\Modules\DemoNotes;

use App\Modules\DemoNotes\Database\Seeders\DemoNoteSeeder;
use App\Modules\DemoNotes\Models\DemoNote;
use App\Modules\DemoNotes\Models\DemoTag;
use App\Modules\DemoNotes\Policies\DemoNotePolicy;
use App\Modules\DemoNotes\Policies\DemoTagPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'DemoNotes';
    }

    public function isInstallable(): bool
    {
        return true;
    }

    public function policies(): array
    {
        return [
            DemoNote::class => DemoNotePolicy::class,
            DemoTag::class => DemoTagPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('demo_notes', 'view', 'View Demo Notes'),
            $this->permissionEntry('demo_notes', 'create', 'Create Demo Notes'),
            $this->permissionEntry('demo_notes', 'edit', 'Edit Demo Notes'),
            $this->permissionEntry('demo_notes', 'delete', 'Delete Demo Notes'),
            $this->permissionEntry('demo_tags', 'view', 'View Demo Tags'),
            $this->permissionEntry('demo_tags', 'create', 'Create Demo Tags'),
            $this->permissionEntry('demo_tags', 'edit', 'Edit Demo Tags'),
            $this->permissionEntry('demo_tags', 'delete', 'Delete Demo Tags'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Demo Notes', 'admin.demo-notes.index', 'demo_notes.view', 'bi-journal-text', sort: 10),
            $this->menuItem('Demo Tags', 'admin.demo-tags.index', 'demo_tags.view', 'bi-tags', sort: 20),
        ];
    }

    public function seeders(): array
    {
        return [DemoNoteSeeder::class];
    }
}
