<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TicketReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SupportTicket $ticket,
        public SupportTicketMessage $message,
        public string $direction = 'to_customer',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        $sender = $this->message->user->name ?? ucfirst($this->message->sender_role);

        return [
            'title'   => 'Reply on Ticket ' . $this->ticket->ticket_number,
            'message' => "{$sender} replied to ticket #{$this->ticket->ticket_number}.",
            'type'    => 'ticket_reply',
            'icon'    => 'tabler-message',
            'url'     => $this->direction === 'to_customer'
                ? url('/my-tickets/' . $this->ticket->id)
                : route('support-tickets.show', $this->ticket->id),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->ticket->loadMissing(['user', 'order', 'messages']);
        $sender = $this->message->user->name ?? ucfirst($this->message->sender_role);
        $hasAttachments = !empty($this->message->attachments);

        if ($this->direction === 'to_customer') {
            $url = url('/my-tickets/' . $this->ticket->id);

            return (new MailMessage)
                ->subject('Reply on Ticket ' . $this->ticket->ticket_number)
                ->view('emails.tickets.reply-customer', [
                    'ticket'         => $this->ticket,
                    'recipientName'  => $notifiable->name ?? 'Customer',
                    'senderName'     => $sender,
                    'messageBody'    => $this->message->message,
                    'hasAttachments' => $hasAttachments,
                    'viewUrl'        => $url,
                ]);
        }

        $url = route('support-tickets.show', $this->ticket->id);

        return (new MailMessage)
            ->subject('Customer Reply: ' . $this->ticket->ticket_number)
            ->view('emails.tickets.reply-admin', [
                'ticket'         => $this->ticket,
                'recipientName'  => $notifiable->name ?? 'Admin',
                'senderName'     => $sender,
                'messageBody'    => $this->message->message,
                'hasAttachments' => $hasAttachments,
                'viewUrl'        => $url,
            ]);
    }
}
