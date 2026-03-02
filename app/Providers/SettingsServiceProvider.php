<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        config([
            'mail.default' => Setting::get('email', 'mailer', config('mail.default')),
            'mail.mailers.smtp.host' => Setting::get('email', 'host', config('mail.mailers.smtp.host')),
            'mail.mailers.smtp.port' => Setting::get('email', 'port', config('mail.mailers.smtp.port')),
            'mail.mailers.smtp.encryption' => Setting::get('email', 'encryption', config('mail.mailers.smtp.encryption')),
            'mail.mailers.smtp.username' => Setting::get('email', 'username', config('mail.mailers.smtp.username')),
            'mail.mailers.smtp.password' => Setting::get('email', 'password', config('mail.mailers.smtp.password')),
            'mail.from.address' => Setting::get('email', 'from_address', config('mail.from.address')),
            'mail.from.name' => Setting::get('email', 'from_name', config('mail.from.name')),
        ]);

        config([
            'app.name' => Setting::get('general', 'site_name', config('app.name')),
            'app.timezone' => Setting::get('general', 'timezone', config('app.timezone')),
        ]);
        date_default_timezone_set(config('app.timezone'));

        $sessionTimeout = Setting::get('security', 'session_timeout_minutes', config('session.lifetime'));
        config(['session.lifetime' => (int) $sessionTimeout]);

        $apiRateLimit = (int) Setting::get('api_integration', 'api_rate_limit_per_minute', 60);
        RateLimiter::for('api', function (Request $request) use ($apiRateLimit) {
            return Limit::perMinute($apiRateLimit)->by($request->user()?->id ?: $request->ip());
        });
    }
}
