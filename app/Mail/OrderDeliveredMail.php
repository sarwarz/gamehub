<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderDeliveredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function build()
    {
        return $this
            ->subject("Your Order {$this->order->order_number} Has Been Delivered")
            ->view('emails.order-delivered');
    }
}
