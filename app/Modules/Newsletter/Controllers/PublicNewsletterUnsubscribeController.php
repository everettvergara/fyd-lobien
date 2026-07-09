<?php

namespace App\Modules\Newsletter\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Newsletter\Services\NewsletterSubscriptionService;
use Illuminate\View\View;

class PublicNewsletterUnsubscribeController extends Controller
{
    public function __construct(
        protected NewsletterSubscriptionService $subscriptions,
    ) {}

    public function __invoke(string $token): View
    {
        $subscriber = $this->subscriptions->unsubscribeByToken($token);

        if ($subscriber === null) {
            abort(404);
        }

        return view('newsletter::public.unsubscribed', [
            'subscriber' => $subscriber,
            'list' => $subscriber->list,
        ]);
    }
}
