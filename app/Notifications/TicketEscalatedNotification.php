<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketEscalatedNotification extends Notification implements ShouldQueue
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
            'title'   => 'Escalated Ticket: ' . $this->ticket->ticket_number,
            'message' => "Ticket #{$this->ticket->ticket_number} has been escalated and requires attention.",
            'type'    => 'ticket_escalated',
            'icon'    => 'tabler-alert-triangle',
            'url'     => route('support-tickets.show', $this->ticket->id),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->ticket->loadMissing(['user', 'seller', 'messages', 'order']);
        $url = route('support-tickets.show', $this->ticket->id);

        return (new MailMessage)
            ->subject('⚠ Escalated Ticket: ' . $this->ticket->ticket_number)
            ->view('emails.tickets.escalated', [
                'ticket'        => $this->ticket,
                'recipientName' => $notifiable->name ?? 'Admin',
                'viewUrl'       => $url,
            ]);
    }
}
