<?php

namespace App\Notifications;

use App\Models\RefundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected RefundRequest $refund,
        protected string $status
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->subject())
            ->line($this->statusLine());

        if ($this->status === 'rejected' && $this->refund->admin_note) {
            $mail->line("Reason: {$this->refund->admin_note}");
        }

        return $mail->action('View Order', url('/my-orders/' . $this->refund->order_id));
    }

    protected function subject(): string
    {
        return match ($this->status) {
            'approved' => 'Refund Approved',
            'completed' => 'Refund Completed',
            default => 'Refund Update',
        };
    }

    protected function statusLine(): string
    {
        $orderNumber = $this->refund->order->order_number;

        return match ($this->status) {
            'approved' => "Your refund request for order #{$orderNumber} has been approved and is being processed.",
            'completed' => "Your refund for order #{$orderNumber} has been processed and credited.",
            'rejected' => "Your refund request for order #{$orderNumber} was not approved.",
            default => "Your refund request for order #{$orderNumber} has been updated.",
        };
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->subject(),
            'message' => $this->statusLine(),
            'icon' => 'tabler-receipt-refund',
            'type' => 'refund',
            'refund_id' => $this->refund->id,
            'order_id' => $this->refund->order_id,
            'status' => $this->status,
        ];
    }
}
