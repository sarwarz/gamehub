<?php

namespace App\Notifications\Seller;

use App\Models\Seller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerApprovedNotification extends Notification implements ShouldQueue
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
            ->subject('Your Seller Application Has Been Approved!')
            ->greeting('Congratulations, ' . ($notifiable->name ?? 'Seller') . '!')
            ->line('Your seller application for **' . $this->seller->store_name . '** has been approved.')
            ->line('You can now start listing products and managing your store.')
            ->action('Go to Seller Dashboard', url('/seller/dashboard'))
            ->line('Thank you for joining our marketplace!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Seller Application Approved',
            'message' => 'Your seller application for ' . $this->seller->store_name . ' has been approved.',
            'type' => 'seller',
            'icon' => 'tabler-circle-check',
            'url' => url('/seller/dashboard'),
        ];
    }
}
