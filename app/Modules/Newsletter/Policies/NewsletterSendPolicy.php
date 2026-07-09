<?php

namespace App\Modules\Newsletter\Policies;

use App\Models\User;
use App\Modules\Newsletter\Models\NewsletterSend;

class NewsletterSendPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('newsletter-sends.view');
    }

    public function view(User $user, NewsletterSend $newsletterSend): bool
    {
        return $user->hasPermission('newsletter-sends.view');
    }

    public function send(User $user): bool
    {
        return $user->hasPermission('newsletters.send');
    }
}
