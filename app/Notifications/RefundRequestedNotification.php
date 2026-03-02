<?php

namespace App\Notifications;

use App\Models\RefundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected RefundRequest $refund,
        protected string $audience = 'customer'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->audience === 'admin') {
            return $this->adminMail();
        }

        return $this->customerMail();
    }

    protected function customerMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Refund Request Received')
            ->line("We've received your refund request for order #{$this->refund->order->order_number}.")
            ->line("Amount: {$this->refund->amount}")
            ->line("We'll review it shortly.")
            ->action('View Order', url('/my-orders/' . $this->refund->order_id));
    }

    protected function adminMail(): MailMessage
    {
        return (new MailMessage)
            ->subject("New Refund Request — Order #{$this->refund->order->order_number}")
            ->line('A refund request has been submitted.')
            ->line("Order: #{$this->refund->order->order_number}")
            ->line("Amount: {$this->refund->amount}")
            ->line("Reason: {$this->refund->reason}")
            ->action('Review Refund', route('refunds.show', $this->refund->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Refund Request',
            'message' => "Refund request for order #{$this->refund->order->order_number}",
            'icon' => 'tabler-receipt-refund',
            'type' => 'refund',
            'refund_id' => $this->refund->id,
            'order_id' => $this->refund->order_id,
        ];
    }
}
