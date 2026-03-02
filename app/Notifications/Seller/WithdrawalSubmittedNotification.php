<?php

namespace App\Notifications\Seller;

use App\Models\SellerWithdraw;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SellerWithdraw $withdraw) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Withdrawal Request Submitted')
            ->line('Your withdrawal request of ' . format_currency($this->withdraw->amount) . ' via ' . $this->withdraw->method . ' has been submitted.')
            ->line('It is now pending admin review.')
            ->action('View Withdrawal', url('/seller/withdrawals'));
    }
}
