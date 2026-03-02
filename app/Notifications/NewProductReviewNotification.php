<?php

namespace App\Notifications;

use App\Models\ProductReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewProductReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected ProductReview $review
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->review->loadMissing(['product', 'user']);

        $mail = (new MailMessage)
            ->subject('New Review on Your Product')
            ->line("{$this->review->user->name} left a {$this->review->rating}-star review on {$this->review->product->title}.");

        if ($this->review->review) {
            $mail->line("\"{$this->review->review}\"");
        }

        return $mail->action('View Reviews', url('/seller/reviews'));
    }

    public function toArray(object $notifiable): array
    {
        $this->review->loadMissing(['product', 'user']);

        return [
            'title' => 'New Product Review',
            'message' => "{$this->review->user->name} left a {$this->review->rating}-star review on {$this->review->product->title}.",
            'icon' => 'tabler-star',
            'type' => 'review',
            'review_id' => $this->review->id,
            'product_id' => $this->review->product_id,
        ];
    }
}
