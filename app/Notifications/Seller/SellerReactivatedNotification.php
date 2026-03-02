<?php

namespace App\Notifications\Seller;

use App\Models\Seller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerReactivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Seller $seller) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Seller Account Has Been Reactivated')
            ->greeting('Great news, ' . ($notifiable->name ?? 'Seller') . '!')
            ->line('Your seller account for **' . $this->seller->store_name . '** has been reactivated.')
            ->line('You can now access your seller dashboard and continue managing your store.')
            ->action('Go to Seller Dashboard', url('/seller/dashboard'))
            ->line('Welcome back to our marketplace!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Seller Account Reactivated',
            'message' => 'Your seller account for ' . $this->seller->store_name . ' has been reactivated.',
            'type' => 'seller',
            'icon' => 'tabler-refresh',
            'url' => url('/seller/dashboard'),
        ];
    }
}
