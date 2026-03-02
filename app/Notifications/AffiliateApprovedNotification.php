<?php

namespace App\Notifications;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AffiliateApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Affiliate $affiliate) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Affiliate Application Approved',
            'message' => "Your affiliate application has been approved! Your referral code is {$this->affiliate->referral_code}.",
            'type'    => 'affiliate',
            'icon'    => 'tabler-affiliate',
            'url'     => url('/affiliate/dashboard'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Affiliate Application Approved')
            ->greeting('Congratulations, ' . ($notifiable->name ?? 'Partner') . '!')
            ->line('Your affiliate application has been approved.')
            ->line('Your referral code: **' . $this->affiliate->referral_code . '**')
            ->line('Share your referral link and earn commissions on every sale.')
            ->action('Go to Affiliate Dashboard', url('/affiliate/dashboard'))
            ->line('Thank you for joining our affiliate program!');
    }
}
