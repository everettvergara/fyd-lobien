<?php

namespace App\Modules\Newsletter;

use App\Framework\PublicBlock;
use App\Modules\Newsletter\Blocks\NewsletterBlockResolver;
use App\Modules\Newsletter\Database\Seeders\DemoNewsletterSeeder;
use App\Modules\Newsletter\Models\NewsletterList;
use App\Modules\Newsletter\Models\NewsletterSend;
use App\Modules\Newsletter\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Policies\NewsletterListPolicy;
use App\Modules\Newsletter\Policies\NewsletterSendPolicy;
use App\Modules\Newsletter\Policies\NewsletterSubscriberPolicy;
use App\Modules\Newsletter\Support\NewsletterListOptionsProvider;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Newsletter';
    }

    public function isInstallable(): bool
    {
        return true;
    }

    public function policies(): array
    {
        return [
            NewsletterList::class => NewsletterListPolicy::class,
            NewsletterSubscriber::class => NewsletterSubscriberPolicy::class,
            NewsletterSend::class => NewsletterSendPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('newsletter-lists', 'view', 'View Newsletter Lists'),
            $this->permissionEntry('newsletter-lists', 'create', 'Create Newsletter Lists'),
            $this->permissionEntry('newsletter-lists', 'edit', 'Edit Newsletter Lists'),
            $this->permissionEntry('newsletter-lists', 'delete', 'Delete Newsletter Lists'),
            $this->permissionEntry('newsletter-subscribers', 'view', 'View Newsletter Subscribers'),
            $this->permissionEntry('newsletter-subscribers', 'create', 'Create Newsletter Subscribers'),
            $this->permissionEntry('newsletter-subscribers', 'edit', 'Edit Newsletter Subscribers'),
            $this->permissionEntry('newsletter-subscribers', 'delete', 'Delete Newsletter Subscribers'),
            $this->permissionEntry('newsletter-subscribers', 'export', 'Export Newsletter Subscribers'),
            $this->permissionEntry('newsletters', 'send', 'Send Newsletters'),
            $this->permissionEntry('newsletter-sends', 'view', 'View Newsletter Send History'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Newsletter Lists', 'admin.newsletter-lists.index', 'newsletter-lists.view', 'bi-envelope-open', sort: 10),
            $this->menuItem('Subscribers', 'admin.newsletter-subscribers.index', 'newsletter-subscribers.view', 'bi-people', sort: 20),
            $this->menuItem('Send Newsletter', 'admin.newsletters.compose', 'newsletters.send', 'bi-send', sort: 30),
            $this->menuItem('Send History', 'admin.newsletter-sends.index', 'newsletter-sends.view', 'bi-clock-history', sort: 40),
        ];
    }

    public function seeders(): array
    {
        return [DemoNewsletterSeeder::class];
    }

    public function publicBlocks(): array
    {
        return [
            PublicBlock::make('newsletter')
                ->label('Newsletter')
                ->icon('bi-envelope')
                ->module($this->name())
                ->resolver(NewsletterBlockResolver::class)
                ->component('NewsletterBlock')
                ->configSchema([
                    [
                        'key' => 'list_slug',
                        'label' => 'Newsletter List',
                        'type' => 'select',
                        'required' => true,
                        'optionsProvider' => NewsletterListOptionsProvider::class,
                    ],
                ]),
        ];
    }
}
