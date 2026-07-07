<?php

namespace App\Modules\Newsletter\Policies;

use App\Models\User;
use App\Modules\Newsletter\Models\NewsletterList;

class NewsletterListPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('newsletter-lists.view');
    }

    public function view(User $user, NewsletterList $newsletterList): bool
    {
        return $user->hasPermission('newsletter-lists.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('newsletter-lists.create');
    }

    public function update(User $user, NewsletterList $newsletterList): bool
    {
        return $user->hasPermission('newsletter-lists.edit');
    }

    public function delete(User $user, NewsletterList $newsletterList): bool
    {
        return $user->hasPermission('newsletter-lists.delete');
    }
}
