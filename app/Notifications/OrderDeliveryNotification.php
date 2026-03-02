<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDeliveryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your Order #{$this->order->order_number} Has Been Delivered!")
            ->greeting('Hi ' . ($notifiable->name ?? 'Customer') . '!')
            ->line("Great news! Your order **#{$this->order->order_number}** has been delivered.")
            ->line('Your product keys/licenses are now available in your account.')
            ->action('View My Order', url('/my-orders/' . $this->order->id))
            ->line('If you have any issues with your keys, please contact support.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Order #{$this->order->order_number} Delivered",
            'message' => "Your product keys for order #{$this->order->order_number} are ready.",
            'type' => 'order',
            'icon' => 'tabler-package',
            'url' => url('/my-orders/' . $this->order->id),
        ];
    }
}
