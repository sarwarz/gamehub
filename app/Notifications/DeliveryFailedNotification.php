<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $audience = 'admin',
        public string $errorMessage = '',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->audience === 'admin'
            ? route('orders.edit', $this->order->id)
            : url('/seller/orders/' . $this->order->id);

        return (new MailMessage)
            ->subject("Delivery Failed — Order #{$this->order->order_number}")
            ->line("Auto-delivery failed for order **#{$this->order->order_number}**.")
            ->line("Error: {$this->errorMessage}")
            ->line('Manual intervention may be required.')
            ->action('View Order', $url);
    }

    public function toArray(object $notifiable): array
    {
        $url = $this->audience === 'admin'
            ? route('orders.edit', $this->order->id)
            : url('/seller/orders/' . $this->order->id);

        return [
            'title' => 'Delivery Failed',
            'message' => "Auto-delivery failed for order #{$this->order->order_number}.",
            'type' => 'order',
            'icon' => 'tabler-alert-triangle',
            'url' => $url,
        ];
    }
}
