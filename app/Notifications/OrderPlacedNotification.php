<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $audience = 'customer'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        $order = $this->order;

        return [
            'title'   => "New Order #{$order->order_number}",
            'message' => $this->audience === 'customer'
                ? "Your order #{$order->order_number} has been placed successfully."
                : "New order #{$order->order_number} from {$order->user?->name}.",
            'type'    => 'order',
            'icon'    => 'tabler-shopping-cart',
            'url'     => $this->audience === 'customer'
                ? url('/my-orders/' . $order->id)
                : route('orders.edit', $order->id),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        $order->loadMissing(['user', 'items.product']);

        if ($this->audience === 'customer') {
            return (new MailMessage)
                ->subject("Order #{$order->order_number} — Thank You for Your Purchase!")
                ->view('emails.orders.placed', [
                    'order'        => $order,
                    'customerName' => $notifiable->name,
                    'viewUrl'      => url('/my-orders/' . $order->id),
                ]);
        }

        $viewUrl = $this->audience === 'admin'
            ? route('orders.edit', $order->id)
            : url('/');

        return (new MailMessage)
            ->subject("New Order #{$order->order_number} Received")
            ->view('emails.orders.placed-admin', [
                'order'         => $order,
                'recipientName' => $notifiable->name,
                'audience'      => $this->audience,
                'viewUrl'       => $viewUrl,
            ]);
    }
}
