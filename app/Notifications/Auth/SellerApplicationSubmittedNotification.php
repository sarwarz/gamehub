<?php

namespace App\Notifications\Auth;

use App\Models\Seller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerApplicationSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Seller $seller) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Seller Application Received — ' . config('app.name'))
            ->view('emails.seller.application-submitted', [
                'recipientName' => $notifiable->name ?? 'there',
                'seller'        => $this->seller,
            ]);
    }
}
