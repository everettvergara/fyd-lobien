<?php

namespace App\Modules\Newsletter\Jobs;

use App\Modules\Newsletter\Models\NewsletterSend;
use App\Modules\Newsletter\Models\NewsletterSendRecipient;
use App\Modules\Newsletter\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessNewsletterSendJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $sendId,
    ) {
        $this->onQueue('newsletters');
    }

    public function handle(): void
    {
        $send = NewsletterSend::query()->find($this->sendId);

        if ($send === null || $send->status !== NewsletterSend::STATUS_QUEUED) {
            return;
        }

        $send->update([
            'status' => NewsletterSend::STATUS_PROCESSING,
            'started_at' => now(),
        ]);

        $subscribers = NewsletterSubscriber::query()
            ->where('newsletter_list_id', $send->newsletter_list_id)
            ->where('status', NewsletterSubscriber::STATUS_ACTIVE)
            ->get();

        if ($subscribers->isEmpty()) {
            $send->update([
                'status' => NewsletterSend::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            return;
        }

        foreach ($subscribers as $subscriber) {
            $recipient = NewsletterSendRecipient::query()->create([
                'newsletter_send_id' => $send->id,
                'newsletter_subscriber_id' => $subscriber->id,
                'email' => $subscriber->email,
                'status' => NewsletterSendRecipient::STATUS_PENDING,
            ]);

            SendNewsletterToSubscriberJob::dispatch($recipient->id);
        }
    }
}
