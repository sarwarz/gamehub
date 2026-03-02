<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactFormNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected ContactMessage $contact
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Contact Message: {$this->contact->subject}")
            ->line("From: {$this->contact->name} ({$this->contact->email})")
            ->line('Message:')
            ->line($this->contact->message)
            ->action('View in Dashboard', route('contact-messages.show', $this->contact->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Contact Message',
            'message' => "From {$this->contact->name}: {$this->contact->subject}",
            'icon' => 'tabler-mail',
            'type' => 'contact',
            'contact_id' => $this->contact->id,
        ];
    }
}
