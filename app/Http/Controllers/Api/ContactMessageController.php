<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\CaptchaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Contact
 *
 * APIs for the website contact form. Users can submit messages
 * without authentication.
 */
class ContactMessageController extends Controller
{
    /**
     * Submit contact message
     *
     * Send a message through the website's contact form.
     * Messages are stored and visible to admins for review and reply.
     *
     * @unauthenticated
     *
     * @bodyParam name string required Your full name. Example: John Doe
     * @bodyParam email string required Your email address. Example: john@example.com
     * @bodyParam phone string optional Your phone number. Example: +1234567890
     * @bodyParam subject string required Message subject. Example: Order inquiry
     * @bodyParam message string required Message body (max 5000 characters). Example: I have a question about my recent order...
     * @bodyParam captcha_token string optional CAPTCHA token for reCAPTCHA v3 or Cloudflare Turnstile (required when CAPTCHA is enabled). No-example
     *
     * @response 201 {"status":true,"message":"Your message has been sent successfully. We will get back to you soon.","data":{"id":1,"name":"John Doe","subject":"Order inquiry","status":"new","created_at":"2026-02-28T10:00:00.000000Z"}}
     * @response 422 {"status":false,"message":"The email field is required."}
     * @response 429 {"status":false,"message":"Too many messages. Please try again later."}
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:30',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        try {
            if ($error = CaptchaService::check($request->input('captcha_token'), 'contact')) {
                return $this->error($error, 422);
            }

            $recentCount = ContactMessage::where('ip_address', $request->ip())
                ->where('created_at', '>=', now()->subHour())
                ->count();

            if ($recentCount >= 5) {
                return $this->error('Too many messages. Please try again later.', 429);
            }

            $data['status']     = 'new';
            $data['ip_address'] = $request->ip();

            $message = ContactMessage::create($data);

            try {
                if (\App\Models\Setting::get('notifications', 'new_contact_message', true)) {
                    $admins = \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin','superadmin']))->get();
                    $admins->each(fn($a) => $a->notify(new \App\Notifications\ContactFormNotification($message)));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Contact form notification failed: ' . $e->getMessage());
            }

            return $this->success(
                $message->only(['id', 'name', 'subject', 'status', 'created_at']),
                'Your message has been sent successfully. We will get back to you soon.',
                201
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to send message');
        }
    }
}
