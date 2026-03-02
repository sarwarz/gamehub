<?php

namespace App\Notifications\Wallet;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletDepositConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected float $amount,
        protected float $newBalance
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Wallet Deposit Confirmed')
            ->line("Your wallet deposit of {$this->amount} has been confirmed.")
            ->line("Your new wallet balance is {$this->newBalance}.")
            ->action('View Wallet', url('/wallet'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Wallet Deposit Confirmed',
            'message' => "Your wallet deposit of {$this->amount} has been confirmed.",
            'icon' => 'tabler-wallet',
            'type' => 'wallet',
            'amount' => $this->amount,
            'new_balance' => $this->newBalance,
        ];
    }
}
