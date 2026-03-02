<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriberWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct() {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name') . ' Newsletter!')
            ->line('Thank you for subscribing!')
            ->line("You'll receive updates about new products, deals, and more.")
            ->action('Browse Products', url('/shop'));
    }
}
