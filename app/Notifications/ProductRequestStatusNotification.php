<?php

namespace App\Notifications;

use App\Models\ProductRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductRequestStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected ProductRequest $productRequest,
        protected string $status
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->status === 'approved'
            ? 'Product Request Approved'
            : 'Product Request Update';

        $line = $this->status === 'approved'
            ? "Your product request \"{$this->productRequest->title}\" has been approved."
            : "Your product request \"{$this->productRequest->title}\" has been updated to: {$this->status}.";

        return (new MailMessage)
            ->subject($subject)
            ->line($line)
            ->action('View My Requests', url('/product-requests'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->status === 'approved'
                ? 'Product Request Approved'
                : 'Product Request Update',
            'message' => "Your product request \"{$this->productRequest->title}\" status: {$this->status}.",
            'icon' => 'tabler-package',
            'type' => 'product_request',
            'product_request_id' => $this->productRequest->id,
            'status' => $this->status,
        ];
    }
}
