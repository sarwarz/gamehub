<?php

namespace App\Notifications\Seller;

use App\Models\SellerWithdraw;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SellerWithdraw $withdraw,
        public string $status,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = format_currency($this->withdraw->amount);

        $mail = (new MailMessage)
            ->subject($this->status === 'approved' ? 'Withdrawal Approved' : 'Withdrawal Update');

        if ($this->status === 'approved') {
            $mail->line("Your withdrawal of {$amount} has been approved and is being processed.");
        } else {
            $mail->line("Your withdrawal of {$amount} was not approved.");

            if ($this->withdraw->note) {
                $mail->line("Reason: {$this->withdraw->note}");
            }

            $mail->line('The amount has been returned to your available balance.');
        }

        return $mail->action('View Details', url('/seller/withdrawals'));
    }

    public function toArray(object $notifiable): array
    {
        $amount = format_currency($this->withdraw->amount);

        return [
            'title' => $this->status === 'approved' ? 'Withdrawal Approved' : 'Withdrawal Not Approved',
            'message' => $this->status === 'approved'
                ? "Your withdrawal of {$amount} has been approved."
                : "Your withdrawal of {$amount} was not approved.",
            'type' => 'withdrawal',
            'icon' => $this->status === 'approved' ? 'tabler-check' : 'tabler-x',
            'url' => url('/seller/withdrawals'),
        ];
    }
}
