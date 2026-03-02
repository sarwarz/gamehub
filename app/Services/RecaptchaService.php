<?php

namespace App\Services;

/**
 * @deprecated Use CaptchaService instead. This wrapper exists for backward compatibility.
 */
class RecaptchaService
{
    public static function isEnabled(): bool
    {
        return CaptchaService::isEnabled();
    }

    public static function verify(?string $token, ?string $expectedAction = null): bool
    {
        return CaptchaService::verify($token, $expectedAction);
    }

    public static function check(?string $token, ?string $action = null): ?string
    {
        return CaptchaService::check($token, $action);
    }
}
