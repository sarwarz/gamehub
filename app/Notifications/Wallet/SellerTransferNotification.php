<?php

namespace App\Notifications\Wallet;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerTransferNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected float $amount,
        protected string $storeName,
        protected float $newBalance
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Seller Balance Transferred to Wallet')
            ->line("{$this->amount} from your seller balance ({$this->storeName}) has been transferred to your wallet.")
            ->line("New wallet balance: {$this->newBalance}")
            ->action('View Wallet', url('/wallet'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Seller Balance Transferred to Wallet',
            'message' => "{$this->amount} from {$this->storeName} transferred to your wallet.",
            'icon' => 'tabler-building-store',
            'type' => 'wallet',
            'amount' => $this->amount,
            'store_name' => $this->storeName,
            'new_balance' => $this->newBalance,
        ];
    }
}
