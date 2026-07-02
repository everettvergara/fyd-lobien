<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to '.config('fyd.name'))
            ->greeting('Welcome, '.$notifiable->name.'!')
            ->line('Thank you for registering with '.config('fyd.name').'.')
            ->line('Please check your inbox for a separate email with a verification link to activate your account.')
            ->line('We are glad to have you on board!');
    }
}
