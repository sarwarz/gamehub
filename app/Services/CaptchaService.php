<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CaptchaService
{
    protected const RECAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    protected const TURNSTILE_VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    protected const RECAPTCHA_MIN_SCORE = 0.5;

    /**
     * Returns the active captcha provider: 'recaptcha', 'turnstile', or 'none'.
     */
    public static function provider(): string
    {
        return Setting::get('api_integration', 'captcha_provider', 'none');
    }

    public static function isEnabled(): bool
    {
        return self::provider() !== 'none';
    }

    /**
     * Verify a captcha token using the active provider.
     * Returns true if captcha is disabled or the token passes verification.
     */
    public static function verify(?string $token, ?string $expectedAction = null): bool
    {
        $provider = self::provider();

        if ($provider === 'none') {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        return match ($provider) {
            'recaptcha' => self::verifyRecaptcha($token, $expectedAction),
            'turnstile' => self::verifyTurnstile($token, $expectedAction),
            default     => true,
        };
    }

    /**
     * Validate and return error response if captcha fails.
     * Returns null on success, or an error message string on failure.
     */
    public static function check(?string $token, ?string $action = null): ?string
    {
        if (!self::verify($token, $action)) {
            return 'CAPTCHA verification failed. Please try again.';
        }

        return null;
    }

    protected static function verifyRecaptcha(string $token, ?string $expectedAction): bool
    {
        $secret = Setting::get('api_integration', 'google_recaptcha_secret_key', '');

        if (empty($secret)) {
            Log::warning('reCAPTCHA secret key is not configured.');
            return true;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post(self::RECAPTCHA_VERIFY_URL, [
                    'secret'   => $secret,
                    'response' => $token,
                ]);

            $body = $response->json();

            if (empty($body['success'])) {
                Log::info('reCAPTCHA verification failed', [
                    'errors' => $body['error-codes'] ?? [],
                ]);
                return false;
            }

            if (($body['score'] ?? 1.0) < self::RECAPTCHA_MIN_SCORE) {
                Log::info('reCAPTCHA score too low', ['score' => $body['score']]);
                return false;
            }

            if ($expectedAction && isset($body['action']) && $body['action'] !== $expectedAction) {
                Log::info('reCAPTCHA action mismatch', [
                    'expected' => $expectedAction,
                    'actual'   => $body['action'],
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA verification request failed: ' . $e->getMessage());
            return true;
        }
    }

    protected static function verifyTurnstile(string $token, ?string $expectedAction): bool
    {
        $secret = Setting::get('api_integration', 'turnstile_secret_key', '');

        if (empty($secret)) {
            Log::warning('Turnstile secret key is not configured.');
            return true;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post(self::TURNSTILE_VERIFY_URL, [
                    'secret'   => $secret,
                    'response' => $token,
                ]);

            $body = $response->json();

            if (empty($body['success'])) {
                Log::info('Turnstile verification failed', [
                    'errors' => $body['error-codes'] ?? [],
                ]);
                return false;
            }

            if ($expectedAction && isset($body['action']) && $body['action'] !== $expectedAction) {
                Log::info('Turnstile action mismatch', [
                    'expected' => $expectedAction,
                    'actual'   => $body['action'],
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Turnstile verification request failed: ' . $e->getMessage());
            return true;
        }
    }
}
