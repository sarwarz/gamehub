<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification implements ShouldQueue
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
            'title'   => 'Ticket Assigned: ' . $this->ticket->ticket_number,
            'message' => "You have been assigned to ticket #{$this->ticket->ticket_number}.",
            'type'    => 'ticket_assigned',
            'icon'    => 'tabler-user-check',
            'url'     => route('support-tickets.show', $this->ticket->id),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->ticket->loadMissing(['user', 'seller', 'messages', 'order']);
        $url = route('support-tickets.show', $this->ticket->id);

        return (new MailMessage)
            ->subject('Ticket Assigned: ' . $this->ticket->ticket_number)
            ->view('emails.tickets.assigned', [
                'ticket'        => $this->ticket,
                'recipientName' => $notifiable->name ?? 'Admin',
                'viewUrl'       => $url,
            ]);
    }
}
