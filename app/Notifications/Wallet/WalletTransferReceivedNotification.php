<?php

namespace App\Notifications\Wallet;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletTransferReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected float $amount,
        protected string $senderName,
        protected float $newBalance
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("You've Received a Wallet Transfer")
            ->line("{$this->senderName} sent you {$this->amount}.")
            ->line("New balance: {$this->newBalance}")
            ->action('View Wallet', url('/wallet'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "You've Received a Wallet Transfer",
            'message' => "{$this->senderName} sent you {$this->amount}.",
            'icon' => 'tabler-arrows-exchange',
            'type' => 'wallet',
            'amount' => $this->amount,
            'sender_name' => $this->senderName,
            'new_balance' => $this->newBalance,
        ];
    }
}
