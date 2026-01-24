<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Mail settings
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

        // App name
        config([
            'app.name' => Setting::get('general', 'site_name', config('app.name')),
        ]);
    }
}
