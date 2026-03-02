<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use App\Models\Setting;
use App\Models\ProductReview;
use App\Observers\ProductReviewObserver;
use App\PaymentGateways\GatewayFactory;
use App\Events\OrderPaid;
use App\Events\OrderCompleted;
use App\Events\OrderCancelled;
use App\Events\OrderRefunded;
use App\Listeners\ActivateSellerEarnings;
use App\Listeners\GenerateInvoice;
use App\Listeners\DispatchAutoDelivery;
use App\Listeners\SendPaymentNotifications;
use App\Listeners\IncrementCouponUsage;
use App\Listeners\StartEscrowHold;
use App\Listeners\SendCompletionNotifications;
use App\Listeners\RevertSellerEarnings;
use App\Listeners\RefundWalletPayment;
use App\Listeners\RestoreCouponUsage;
use App\Listeners\SendCancellationNotifications;
use App\Listeners\RevertSellerEarningsOnRefund;
use App\Listeners\RefundWalletPaymentOnRefund;
use App\Listeners\SendRefundNotifications;
use App\Listeners\ProcessAffiliateCommission;
use App\Listeners\HoldAffiliateCommission;
use App\Listeners\ReverseAffiliateCommission;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\CurrencyService::class, function ($app) {
            return new \App\Services\CurrencyService();
        });

        $this->app->singleton(GatewayFactory::class);
    }

    public function boot(): void
    {
        ProductReview::observe(ProductReviewObserver::class);

        Event::listen(OrderPaid::class, ActivateSellerEarnings::class);
        Event::listen(OrderPaid::class, GenerateInvoice::class);
        Event::listen(OrderPaid::class, DispatchAutoDelivery::class);
        Event::listen(OrderPaid::class, SendPaymentNotifications::class);
        Event::listen(OrderPaid::class, IncrementCouponUsage::class);
        Event::listen(OrderPaid::class, ProcessAffiliateCommission::class);

        Event::listen(OrderCompleted::class, StartEscrowHold::class);
        Event::listen(OrderCompleted::class, SendCompletionNotifications::class);
        Event::listen(OrderCompleted::class, HoldAffiliateCommission::class);

        Event::listen(OrderCancelled::class, RevertSellerEarnings::class);
        Event::listen(OrderCancelled::class, RefundWalletPayment::class);
        Event::listen(OrderCancelled::class, RestoreCouponUsage::class);
        Event::listen(OrderCancelled::class, SendCancellationNotifications::class);
        Event::listen(OrderCancelled::class, ReverseAffiliateCommission::class);

        Event::listen(OrderRefunded::class, RevertSellerEarningsOnRefund::class);
        Event::listen(OrderRefunded::class, RefundWalletPaymentOnRefund::class);
        Event::listen(OrderRefunded::class, SendRefundNotifications::class);
        Event::listen(OrderRefunded::class, ReverseAffiliateCommission::class);

        View::composer('*', function ($view) {
            if (Auth::check() && !Auth::user()->relationLoaded('profile')) {
                Auth::user()->load(['profile', 'roles']);
            }
        });

        View::share('appSettings', [
            'site_name'       => Setting::get('general', 'site_name', config('app.name')),
            'tagline'         => Setting::get('general', 'tagline', ''),
            'contact_email'   => Setting::get('general', 'contact_email', ''),
            'contact_phone'   => Setting::get('general', 'contact_phone', ''),
            'logo'            => Setting::get('branding', 'logo'),
            'logo_dark'       => Setting::get('branding', 'logo_dark'),
            'favicon'         => Setting::get('branding', 'favicon'),
            'primary_color'   => Setting::get('branding', 'primary_color', '#7367f0'),
            'secondary_color' => Setting::get('branding', 'secondary_color', '#a8aaae'),
            'footer_text'     => Setting::get('branding', 'footer_text', ''),
            'timezone'        => Setting::get('general', 'timezone', 'UTC'),
            'date_format'     => Setting::get('general', 'date_format', 'M d, Y'),
            'per_page'        => (int) Setting::get('general', 'per_page', 15),
        ]);
    }
}
