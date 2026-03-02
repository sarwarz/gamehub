<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderNoteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $noteText,
        public string $adminName = 'Support Team',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => "New message on Order #{$this->order->order_number}",
            'message' => $this->noteText,
            'type'    => 'order_note',
            'icon'    => 'tabler-message',
            'url'     => url('/my-orders/' . $this->order->id),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->order->loadMissing(['items.product']);

        return (new MailMessage)
            ->subject("New Message on Order #{$this->order->order_number}")
            ->view('emails.orders.note', [
                'order'        => $this->order,
                'noteText'     => $this->noteText,
                'customerName' => $notifiable->name,
                'adminName'    => $this->adminName,
                'viewUrl'      => url('/my-orders/' . $this->order->id),
            ]);
    }
}
