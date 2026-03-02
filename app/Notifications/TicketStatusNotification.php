<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SupportTicket $ticket,
        public string $newStatus,
        public ?string $oldStatus = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        $label = ucwords(str_replace('_', ' ', $this->newStatus));

        return [
            'title'   => "Ticket {$this->ticket->ticket_number} — {$label}",
            'message' => "Ticket #{$this->ticket->ticket_number} status changed to {$label}.",
            'type'    => 'ticket_status',
            'icon'    => 'tabler-info-circle',
            'url'     => url('/my-tickets/' . $this->ticket->id),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->ticket->loadMissing(['user', 'order']);
        $label = ucwords(str_replace('_', ' ', $this->newStatus));

        return (new MailMessage)
            ->subject('Ticket ' . $this->ticket->ticket_number . ' — ' . $label)
            ->view('emails.tickets.status-changed', [
                'ticket'        => $this->ticket,
                'newStatus'     => $this->newStatus,
                'oldStatus'     => $this->oldStatus,
                'recipientName' => $notifiable->name ?? 'Customer',
                'viewUrl'       => url('/my-tickets/' . $this->ticket->id),
            ]);
    }
}
