<?php

namespace App\Modules\Newsletter\Jobs;

use App\Modules\Newsletter\Models\NewsletterSend;
use App\Modules\Newsletter\Models\NewsletterSendRecipient;
use App\Modules\Newsletter\Notifications\NewsletterNotification;
use App\Services\ActivityLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendNewsletterToSubscriberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 120, 300];

    public function __construct(
        public int $recipientId,
    ) {
        $this->onQueue('newsletters');
    }

    public function handle(): void
    {
        $recipient = NewsletterSendRecipient::query()
            ->with(['send', 'subscriber'])
            ->find($this->recipientId);

        if ($recipient === null || $recipient->status !== NewsletterSendRecipient::STATUS_PENDING) {
            return;
        }

        $send = $recipient->send;
        $subscriber = $recipient->subscriber;

        if ($send === null || $subscriber === null) {
            $recipient->update([
                'status' => NewsletterSendRecipient::STATUS_FAILED,
                'error_message' => 'Send or subscriber record missing.',
            ]);
            $this->incrementFailedAndMaybeComplete($send);

            return;
        }

        try {
            $subscriber->notifyNow(new NewsletterNotification(
                subject: (string) $send->subject,
                htmlBody: (string) $send->body,
                unsubscribeUrl: route('newsletters.unsubscribe', ['token' => $subscriber->unsubscribe_token]),
            ));

            $recipient->update([
                'status' => NewsletterSendRecipient::STATUS_SENT,
                'sent_at' => now(),
            ]);

            $send->increment('sent_count');
        } catch (Throwable $exception) {
            $recipient->update([
                'status' => NewsletterSendRecipient::STATUS_FAILED,
                'error_message' => $exception->getMessage(),
            ]);

            $send->increment('failed_count');
        }

        $this->maybeCompleteSend($send->fresh());
    }

    protected function incrementFailedAndMaybeComplete(?NewsletterSend $send): void
    {
        if ($send === null) {
            return;
        }

        $send->increment('failed_count');
        $this->maybeCompleteSend($send->fresh());
    }

    protected function maybeCompleteSend(?NewsletterSend $send): void
    {
        if ($send === null || $send->status !== NewsletterSend::STATUS_PROCESSING) {
            return;
        }

        $processed = (int) $send->sent_count + (int) $send->failed_count;

        if ($processed < (int) $send->recipient_count) {
            return;
        }

        $status = ((int) $send->failed_count) > 0 && ((int) $send->sent_count) === 0
            ? NewsletterSend::STATUS_FAILED
            : NewsletterSend::STATUS_COMPLETED;

        $send->update([
            'status' => $status,
            'completed_at' => now(),
        ]);

        if ($status === NewsletterSend::STATUS_COMPLETED) {
            ActivityLogger::log('newsletter', 'sent', $send, [
                'list_id' => $send->newsletter_list_id,
                'recipient_count' => $send->recipient_count,
                'sent_count' => $send->sent_count,
                'failed_count' => $send->failed_count,
            ]);
        }
    }
}
