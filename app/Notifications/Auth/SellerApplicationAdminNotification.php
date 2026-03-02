<?php

namespace App\Notifications\Auth;

use App\Models\Seller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerApplicationAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Seller $seller) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->seller->loadMissing('user');

        return (new MailMessage)
            ->subject('New Seller Application: ' . $this->seller->store_name)
            ->view('emails.seller.application-admin', [
                'recipientName' => $notifiable->name ?? 'Admin',
                'seller'        => $this->seller,
            ]);
    }
}
