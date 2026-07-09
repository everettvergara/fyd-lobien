<?php

namespace App\Modules\Newsletter\Policies;

use App\Models\User;
use App\Modules\Newsletter\Models\NewsletterSubscriber;

class NewsletterSubscriberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('newsletter-subscribers.view');
    }

    public function view(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $user->hasPermission('newsletter-subscribers.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('newsletter-subscribers.create');
    }

    public function update(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $user->hasPermission('newsletter-subscribers.edit');
    }

    public function delete(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $user->hasPermission('newsletter-subscribers.delete');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('newsletter-subscribers.export');
    }
}
