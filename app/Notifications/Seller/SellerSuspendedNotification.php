<?php

namespace App\Notifications\Seller;

use App\Models\Seller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerSuspendedNotification extends Notification implements ShouldQueue
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
            ->subject('Your Seller Account Has Been Suspended')
            ->greeting('Hi ' . ($notifiable->name ?? 'Seller') . ',')
            ->line('Your seller account for **' . $this->seller->store_name . '** has been suspended.')
            ->line('If you believe this is an error, please contact our support team.')
            ->action('Contact Support', url('/contact'))
            ->line('Thank you for your understanding.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Seller Account Suspended',
            'message' => 'Your seller account for ' . $this->seller->store_name . ' has been suspended.',
            'type' => 'seller',
            'icon' => 'tabler-ban',
            'url' => url('/contact'),
        ];
    }
}
