<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\CheckoutSession;
use App\Models\SellerEarning;
use App\Services\CheckoutService;
use App\Services\KeyReservationService;
use App\Services\SellerBalanceService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

Schedule::call(function () {
    $sessions = CheckoutSession::whereIn('status', ['open', 'paying'])
        ->where('expires_at', '<', now())
        ->limit(100)
        ->get();

    $checkout = app(CheckoutService::class);

    foreach ($sessions as $session) {
        try {
            $checkout->expireSession($session);
        } catch (\Throwable $e) {
            report($e);
        }
    }
})->everyFiveMinutes()->name('expire-checkout-sessions')->withoutOverlapping();

Schedule::call(function () {
    $released = app(KeyReservationService::class)->cleanupExpired();

    if ($released > 0) {
        \Illuminate\Support\Facades\Log::info("Released {$released} expired key reservations.");
    }
})->everyFiveMinutes()->name('release-expired-key-reservations')->withoutOverlapping();

Schedule::call(function () {
    $earnings = SellerEarning::where('status', 'held')
        ->where('escrow_expires_at', '<', now())
        ->get()
        ->pluck('order_id')
        ->unique();

    $service = app(SellerBalanceService::class);

    foreach ($earnings as $orderId) {
        try {
            $service->releaseEscrow($orderId);
        } catch (\Throwable $e) {
            report($e);
        }
    }
})->daily()->name('release-escrow-earnings')->withoutOverlapping();
