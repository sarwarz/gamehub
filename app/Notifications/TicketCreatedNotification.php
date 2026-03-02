<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SupportTicket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'New Ticket: ' . $this->ticket->ticket_number,
            'message' => 'New support ticket from ' . ($this->ticket->user?->name ?? 'a user') . '.',
            'type'    => 'ticket',
            'icon'    => 'tabler-lifebuoy',
            'url'     => route('support-tickets.show', $this->ticket->id),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->ticket->loadMissing(['user', 'seller', 'messages', 'order']);
        $url = route('support-tickets.show', $this->ticket->id);

        return (new MailMessage)
            ->subject('New Support Ticket: ' . $this->ticket->ticket_number)
            ->view('emails.tickets.created', [
                'ticket'        => $this->ticket,
                'recipientName' => $notifiable->name ?? 'Admin',
                'viewUrl'       => $url,
            ]);
    }
}
