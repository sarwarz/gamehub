<?php

namespace App\Notifications;

use App\Models\Affiliate;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AffiliateCommissionEarnedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Affiliate $affiliate,
        public Order $order,
        public float $amount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Commission Earned',
            'message' => 'You earned $' . number_format($this->amount, 2) . ' commission from order #' . $this->order->order_number . '.',
            'type'    => 'affiliate_commission',
            'icon'    => 'tabler-coin',
            'url'     => url('/affiliate/commissions'),
        ];
    }
}
