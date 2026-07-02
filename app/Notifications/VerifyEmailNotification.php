<?php

namespace App\Notifications;

use App\Support\EmailVerificationUrl;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    protected function verificationUrl($notifiable): string
    {
        return EmailVerificationUrl::forUser($notifiable);
    }

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Please click the button below to verify your email address and activate your account.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('If the button does not work, copy and paste this URL into your browser:')
            ->line($verificationUrl)
            ->line('If you did not create an account, no further action is required.');
    }
}
