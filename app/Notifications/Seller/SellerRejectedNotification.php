<?php

namespace App\Notifications\Seller;

use App\Models\Seller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerRejectedNotification extends Notification implements ShouldQueue
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
            ->subject('Seller Application Update')
            ->greeting('Hi ' . ($notifiable->name ?? 'Seller') . ',')
            ->line('Your seller application for **' . $this->seller->store_name . '** was not approved at this time.')
            ->line('You can review your application and resubmit it for consideration.')
            ->line('If you have any questions, please contact our support team.')
            ->action('Contact Support', url('/contact'))
            ->line('Thank you for your interest in our marketplace.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Seller Application Rejected',
            'message' => 'Your seller application for ' . $this->seller->store_name . ' was not approved.',
            'type' => 'seller',
            'icon' => 'tabler-x',
            'url' => url('/seller-application/status'),
        ];
    }
}
