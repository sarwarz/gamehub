<?php

namespace App\Notifications;

use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPaymentNotification extends Notification implements ShouldQueue
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
            'title'   => "Payment Confirmed — #{$order->order_number}",
            'message' => $this->audience === 'customer'
                ? "Payment for order #{$order->order_number} has been confirmed."
                : "Payment received for order #{$order->order_number}.",
            'type'    => 'payment',
            'icon'    => 'tabler-credit-card',
            'url'     => $this->audience === 'customer'
                ? url('/my-orders/' . $order->id)
                : route('orders.edit', $order->id),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        $order->loadMissing(['user', 'items.product', 'invoice']);

        if ($this->audience === 'customer') {
            $mail = (new MailMessage)
                ->subject("Payment Confirmed — Order #{$order->order_number}")
                ->view('emails.orders.payment-confirmed', [
                    'order'        => $order,
                    'customerName' => $notifiable->name,
                    'viewUrl'      => url('/my-orders/' . $order->id),
                ]);

            $pdf = InvoiceService::getPdfForOrder($order);
            if ($pdf) {
                [$filename, $content] = $pdf;
                $mail->attachData($content, $filename, ['mime' => 'application/pdf']);
            }

            return $mail;
        }

        return (new MailMessage)
            ->subject("Payment Received — Order #{$order->order_number}")
            ->view('emails.orders.placed-admin', [
                'order'         => $order,
                'recipientName' => $notifiable->name,
                'audience'      => 'admin',
                'viewUrl'       => route('orders.edit', $order->id),
            ]);
    }
}
