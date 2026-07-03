<?php

namespace App\Modules\Newsletter\Services;

use App\Modules\Newsletter\Models\NewsletterList;
use App\Modules\Newsletter\Models\NewsletterSubscriber;
use Illuminate\Support\Str;

class NewsletterSubscriptionService
{
    public function isSubscribed(NewsletterList $list, string $email): bool
    {
        return NewsletterSubscriber::query()
            ->where('newsletter_list_id', $list->id)
            ->where('email', $email)
            ->where('status', NewsletterSubscriber::STATUS_ACTIVE)
            ->exists();
    }

    public function subscribe(NewsletterList $list, string $email, ?string $name = null, ?int $userId = null): NewsletterSubscriber
    {
        $subscriber = NewsletterSubscriber::query()->firstOrNew([
            'newsletter_list_id' => $list->id,
            'email' => $email,
        ]);

        if (! $subscriber->exists || $subscriber->unsubscribe_token === null) {
            $subscriber->unsubscribe_token = Str::random(64);
        }

        $subscriber->fill([
            'name' => $name,
            'user_id' => $userId,
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
        ]);

        $subscriber->save();

        return $subscriber;
    }

    public function unsubscribe(NewsletterList $list, string $email): bool
    {
        $subscriber = NewsletterSubscriber::query()
            ->where('newsletter_list_id', $list->id)
            ->where('email', $email)
            ->where('status', NewsletterSubscriber::STATUS_ACTIVE)
            ->first();

        if ($subscriber === null) {
            return false;
        }

        $subscriber->update([
            'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
            'unsubscribed_at' => now(),
        ]);

        return true;
    }

    public function unsubscribeByToken(string $token): ?NewsletterSubscriber
    {
        $subscriber = NewsletterSubscriber::query()
            ->with('list')
            ->where('unsubscribe_token', $token)
            ->where('status', NewsletterSubscriber::STATUS_ACTIVE)
            ->first();

        if ($subscriber === null) {
            return null;
        }

        $subscriber->update([
            'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
            'unsubscribed_at' => now(),
        ]);

        return $subscriber->fresh(['list']);
    }
}
