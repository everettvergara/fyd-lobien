<?php

namespace App\Modules\Newsletter\Services;

use App\Models\User;
use App\Modules\Newsletter\Jobs\ProcessNewsletterSendJob;
use App\Modules\Newsletter\Models\NewsletterList;
use App\Modules\Newsletter\Models\NewsletterSend;
use App\Modules\Newsletter\Models\NewsletterSubscriber;
use App\Services\ActivityLogger;

class NewsletterSendService
{
    public function dispatch(NewsletterList $list, string $subject, string $body, ?User $sender = null): NewsletterSend
    {
        $recipientCount = NewsletterSubscriber::query()
            ->where('newsletter_list_id', $list->id)
            ->where('status', NewsletterSubscriber::STATUS_ACTIVE)
            ->count();

        $send = NewsletterSend::create([
            'newsletter_list_id' => $list->id,
            'subject' => $subject,
            'body' => $body,
            'recipient_count' => $recipientCount,
            'sent_by' => $sender?->id,
            'status' => NewsletterSend::STATUS_QUEUED,
            'queued_at' => now(),
            'sent_at' => null,
        ]);

        ProcessNewsletterSendJob::dispatch($send->id);

        ActivityLogger::log('newsletter', 'queued', $send, [
            'list_id' => $list->id,
            'recipient_count' => $recipientCount,
        ]);

        return $send;
    }
}
