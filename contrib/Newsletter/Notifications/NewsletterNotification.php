<?php

namespace App\Modules\Newsletter\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewsletterNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $subject,
        public string $htmlBody,
        public string $unsubscribeUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->view('newsletter::emails.newsletter', [
                'body' => $this->htmlBody,
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ]);
    }
}
