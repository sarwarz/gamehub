<?php

namespace App\Notifications;

use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private const SUBJECTS = [
        'processing' => 'Your Order is Being Processed',
        'completed'  => 'Your Order is Complete!',
        'cancelled'  => 'Your Order Has Been Cancelled',
        'refunded'   => 'Your Order Has Been Refunded',
    ];

    private const VIEWS = [
        'processing' => 'emails.orders.processing',
        'completed'  => 'emails.orders.completed',
        'cancelled'  => 'emails.orders.cancelled',
        'refunded'   => 'emails.orders.refunded',
    ];

    public function __construct(
        public Order $order,
        public string $newStatus,
        public string $audience = 'customer'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        $order = $this->order;
        $label = ucfirst($this->newStatus);

        return [
            'title'   => "Order #{$order->order_number} — {$label}",
            'message' => "Order #{$order->order_number} status changed to {$label}.",
            'type'    => 'order_status',
            'icon'    => 'tabler-package',
            'url'     => $this->audience === 'customer'
                ? url('/my-orders/' . $order->id)
                : route('orders.edit', $order->id),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        $order->loadMissing(['user', 'items.product', 'items.deliveries', 'invoice']);
        $label = ucfirst($this->newStatus);

        if ($this->audience === 'customer') {
            $subject = (self::SUBJECTS[$this->newStatus] ?? "Order Status: {$label}")
                . " — Order #{$order->order_number}";

            $view = self::VIEWS[$this->newStatus] ?? 'emails.orders.processing';

            $mail = (new MailMessage)
                ->subject($subject)
                ->view($view, [
                    'order'        => $order,
                    'status'       => $this->newStatus,
                    'customerName' => $notifiable->name,
                    'viewUrl'      => url('/my-orders/' . $order->id),
                ]);

            if ($this->newStatus === 'completed') {
                $pdf = InvoiceService::getPdfForOrder($order);
                if ($pdf) {
                    [$filename, $content] = $pdf;
                    $mail->attachData($content, $filename, ['mime' => 'application/pdf']);
                }
            }

            return $mail;
        }

        $viewUrl = $this->audience === 'admin'
            ? route('orders.edit', $order->id)
            : url('/');

        return (new MailMessage)
            ->subject("Order #{$order->order_number} — Status: {$label}")
            ->view('emails.orders.status-seller', [
                'order'         => $order,
                'status'        => $this->newStatus,
                'audience'      => $this->audience,
                'recipientName' => $notifiable->name,
                'viewUrl'       => $viewUrl,
            ]);
    }
}
