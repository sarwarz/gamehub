<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Services\CaptchaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Newsletter
 *
 * APIs for newsletter subscription management.
 * Subscribe and unsubscribe endpoints are public (no auth required).
 */
class SubscriberController extends Controller
{
    /**
     * Subscribe to newsletter
     *
     * Add an email address to the newsletter mailing list.
     * If the email was previously unsubscribed, it will be reactivated.
     *
     * @unauthenticated
     *
     * @bodyParam email string required Email address to subscribe. Example: user@example.com
     * @bodyParam name string optional Subscriber name. Example: John Doe
     * @bodyParam captcha_token string optional CAPTCHA token for reCAPTCHA v3 or Cloudflare Turnstile (required when CAPTCHA is enabled). No-example
     *
     * @response 201 {"status":true,"message":"Subscribed successfully.","data":{"id":1,"email":"user@example.com","status":"active"}}
     * @response 200 {"status":true,"message":"You are already subscribed."}
     * @response 200 {"status":true,"message":"Welcome back! Your subscription has been reactivated."}
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'name'  => 'nullable|string|max:255',
        ]);

        try {
            if ($error = CaptchaService::check($request->input('captcha_token'), 'subscribe')) {
                return $this->error($error, 422);
            }

            $existing = Subscriber::where('email', $request->email)->first();

            if ($existing) {
                if ($existing->status === 'active') {
                    return $this->success(null, 'You are already subscribed.');
                }

                $existing->update([
                    'status'          => 'active',
                    'name'            => $request->name ?? $existing->name,
                    'subscribed_at'   => now(),
                    'unsubscribed_at' => null,
                ]);

                return $this->success(
                    $existing->fresh()->only(['id', 'email', 'is_active', 'created_at']),
                    'Welcome back! Your subscription has been reactivated.'
                );
            }

            $subscriber = Subscriber::create([
                'email'         => $request->email,
                'name'          => $request->name,
                'status'        => 'active',
                'subscribed_at' => now(),
                'ip_address'    => $request->ip(),
            ]);

            try {
                if (\App\Models\Setting::get('notifications', 'subscriber_welcome', true)) {
                    $subscriber->notify(new \App\Notifications\SubscriberWelcomeNotification());
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Subscriber welcome notification failed: ' . $e->getMessage());
            }

            return $this->success(
                $subscriber->only(['id', 'email', 'is_active', 'created_at']),
                'Subscribed successfully.',
                201
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to subscribe');
        }
    }

    /**
     * Unsubscribe from newsletter
     *
     * Remove an email address from the newsletter mailing list.
     * The record is kept but marked as unsubscribed.
     *
     * @unauthenticated
     *
     * @bodyParam email string required Email address to unsubscribe. Example: user@example.com
     *
     * @response 200 {"status":true,"message":"You have been unsubscribed successfully."}
     * @response 404 {"status":false,"message":"Email not found in our subscriber list."}
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $subscriber = Subscriber::where('email', $request->email)->first();

            if (!$subscriber) {
                return $this->error('Email not found in our subscriber list.', 404);
            }

            if ($subscriber->status === 'unsubscribed') {
                return $this->success(null, 'You are already unsubscribed.');
            }

            $subscriber->update([
                'status'          => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);

            return $this->success(null, 'You have been unsubscribed successfully.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to unsubscribe');
        }
    }

    /**
     * Check subscription status
     *
     * Check if an email address is subscribed to the newsletter.
     *
     * @unauthenticated
     *
     * @queryParam email string required Email to check. Example: user@example.com
     *
     * @response 200 {"status":true,"message":"Checked","data":{"subscribed":true,"email":"user@example.com"}}
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        try {
            $subscriber = Subscriber::where('email', $request->email)->first();

            return $this->success([
                'subscribed' => $subscriber && $subscriber->status === 'active',
                'email'      => $request->email,
            ], 'Checked');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to check subscription status');
        }
    }
}
