<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = Config::get('auth.passwords.' . Config::get('auth.defaults.passwords') . '.expire', 60);

        return (new MailMessage)
            ->subject('Reset Your Password — ' . config('app.name'))
            ->view('emails.auth.reset-password', [
                'recipientName' => $notifiable->name ?? 'there',
                'resetUrl'      => $resetUrl,
                'expireMinutes'  => $expireMinutes,
            ]);
    }
}
