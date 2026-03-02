<?php

namespace App\Notifications;

use App\Models\AffiliateWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AffiliateWithdrawalProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AffiliateWithdrawal $withdrawal,
        public string $action,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->action === 'approved' ? 'approved' : 'rejected';

        return [
            'title'   => 'Withdrawal ' . ucfirst($status),
            'message' => "Your withdrawal request #{$this->withdrawal->trx} for \${$this->withdrawal->amount} has been {$status}.",
            'type'    => 'affiliate_withdrawal',
            'icon'    => $status === 'approved' ? 'tabler-circle-check' : 'tabler-circle-x',
            'url'     => url('/affiliate/withdrawals'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->action === 'approved' ? 'Approved' : 'Rejected';

        $mail = (new MailMessage)
            ->subject("Affiliate Withdrawal {$status} — {$this->withdrawal->trx}")
            ->greeting('Hello, ' . ($notifiable->name ?? 'Partner') . '!')
            ->line("Your withdrawal request **{$this->withdrawal->trx}** for **\${$this->withdrawal->amount}** has been **{$status}**.");

        if ($this->action === 'rejected' && $this->withdrawal->rejection_reason) {
            $mail->line('Reason: ' . $this->withdrawal->rejection_reason);
        }

        return $mail->action('View Withdrawals', url('/affiliate/withdrawals'));
    }
}
