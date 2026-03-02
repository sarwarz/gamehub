<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Currency;
use App\Models\WalletSetting;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use App\Services\CurrencyService;

class SettingController extends Controller
{
    /* ================================================================
     *  PLATFORM
     * ================================================================ */

    public function general()
    {
        $settings   = Setting::group('general');
        $currencies = Currency::where('is_active', true)->get();
        return view('content.settings.general', compact('settings', 'currencies'));
    }

    public function updateGeneral(Request $request)
    {
        $keys = ['site_name','tagline','contact_email','contact_phone','currency','timezone','date_format','per_page'];
        foreach ($keys as $key) {
            if ($request->has("general.$key")) {
                $value = $request->input("general.$key");
                if ($key === 'currency') {
                    $this->syncDefaultCurrency($value);
                }
                Setting::set('general', $key, $value);
            }
        }
        Cache::forget('settings.group.general');
        return response()->json(['message' => 'General settings saved successfully.']);
    }

    public function branding()
    {
        $settings = Setting::group('branding');
        return view('content.settings.branding', compact('settings'));
    }

    public function updateBranding(Request $request)
    {
        $keys = ['primary_color','secondary_color','footer_text'];
        foreach ($keys as $key) {
            if ($request->has("branding.$key")) {
                Setting::set('branding', $key, $request->input("branding.$key"));
            }
        }

        foreach (['logo','logo_dark','favicon'] as $key) {
            $fieldName = "branding_file_{$key}";
            if ($request->hasFile($fieldName)) {
                $file     = $request->file($fieldName);
                $filename = $key . '_' . time() . '.' . $file->getClientOriginalExtension();
                $dir      = public_path('uploads/branding');

                if (!File::isDirectory($dir)) {
                    File::makeDirectory($dir, 0755, true);
                }
                $oldPath = Setting::get('branding', $key);
                if ($oldPath && File::exists(public_path($oldPath))) {
                    File::delete(public_path($oldPath));
                }
                $file->move($dir, $filename);
                Setting::set('branding', $key, '/uploads/branding/' . $filename);
            }
        }
        Cache::forget('settings.group.branding');
        return response()->json(['message' => 'Branding settings saved successfully.']);
    }

    public function security()
    {
        $settings = Setting::group('security');
        return view('content.settings.security', compact('settings'));
    }

    public function updateSecurity(Request $request)
    {
        $booleans = ['two_factor_enabled','password_require_uppercase','password_require_number','password_require_symbol','ip_whitelist_enabled'];
        $numerics = ['session_timeout_minutes','password_min_length','max_login_attempts','lockout_duration_minutes'];

        foreach ($booleans as $key) {
            Setting::set('security', $key, $request->boolean("security.$key"));
        }
        foreach ($numerics as $key) {
            if ($request->has("security.$key")) {
                Setting::set('security', $key, $request->input("security.$key"));
            }
        }
        if ($request->has('security.ip_whitelist')) {
            $raw = $request->input('security.ip_whitelist', '');
            $ips = is_array($raw) ? $raw : array_filter(array_map('trim', explode("\n", $raw)));
            Setting::set('security', 'ip_whitelist', $ips);
        }
        Cache::forget('settings.group.security');
        return response()->json(['message' => 'Security settings saved successfully.']);
    }

    public function registration()
    {
        $settings = Setting::group('registration');
        return view('content.settings.registration', compact('settings'));
    }

    public function updateRegistration(Request $request)
    {
        $booleans = ['require_email_verification','allow_social_login','require_phone_number','registration_enabled','welcome_email_enabled'];
        foreach ($booleans as $key) {
            Setting::set('registration', $key, $request->boolean("registration.$key"));
        }
        foreach (['min_age_required','auto_assign_role'] as $key) {
            if ($request->has("registration.$key")) {
                Setting::set('registration', $key, $request->input("registration.$key"));
            }
        }
        if ($request->has('registration.social_providers')) {
            Setting::set('registration', 'social_providers', $request->input('registration.social_providers', []));
        }
        Cache::forget('settings.group.registration');
        return response()->json(['message' => 'Registration settings saved successfully.']);
    }

    /* ================================================================
     *  COMMERCE
     * ================================================================ */

    public function store()
    {
        $settings = Setting::group('store');
        return view('content.settings.store', compact('settings'));
    }

    public function updateStore(Request $request)
    {
        $booleans = ['guest_checkout','tax_enabled','show_out_of_stock'];
        $fields   = ['order_prefix','min_order_amount','auto_cancel_hours','tax_display','low_stock_threshold'];
        foreach ($booleans as $key) {
            Setting::set('store', $key, $request->boolean("store.$key"));
        }
        foreach ($fields as $key) {
            if ($request->has("store.$key")) {
                Setting::set('store', $key, $request->input("store.$key"));
            }
        }
        Cache::forget('settings.group.store');
        return response()->json(['message' => 'Store settings saved successfully.']);
    }

    public function checkout()
    {
        $settings = Setting::group('checkout');
        return view('content.settings.checkout', compact('settings'));
    }

    public function updateCheckout(Request $request)
    {
        $booleans = ['require_billing_address','guest_checkout_enabled'];
        $numerics = ['session_timeout_minutes','key_reservation_minutes','max_items_per_order','payment_retry_limit','abandoned_cleanup_minutes'];
        foreach ($booleans as $key) {
            Setting::set('checkout', $key, $request->boolean("checkout.$key"));
        }
        foreach ($numerics as $key) {
            if ($request->has("checkout.$key")) {
                Setting::set('checkout', $key, $request->input("checkout.$key"));
            }
        }
        Cache::forget('settings.group.checkout');
        return response()->json(['message' => 'Checkout settings saved successfully.']);
    }

    public function products()
    {
        $settings = Setting::group('product');
        return view('content.settings.product', compact('settings'));
    }

    public function updateProducts(Request $request)
    {
        $booleans = ['auto_approve_products','require_product_description','allow_product_requests'];
        $numerics = ['max_images_per_product','max_image_size_mb','max_offers_per_product','min_description_length'];
        foreach ($booleans as $key) {
            Setting::set('product', $key, $request->boolean("product.$key"));
        }
        foreach ($numerics as $key) {
            if ($request->has("product.$key")) {
                Setting::set('product', $key, $request->input("product.$key"));
            }
        }
        if ($request->has('product.allowed_image_types')) {
            Setting::set('product', 'allowed_image_types', $request->input('product.allowed_image_types', []));
        }
        Cache::forget('settings.group.product');
        return response()->json(['message' => 'Product settings saved successfully.']);
    }

    public function vendor()
    {
        $settings = Setting::group('vendor');
        return view('content.settings.vendor', compact('settings'));
    }

    public function updateVendor(Request $request)
    {
        $booleans = ['registration_enabled','auto_approve','require_documents'];
        $fields   = ['commission_mode','commission_rate','commission_type',
                     'min_withdrawal','payout_schedule','hold_period_days','max_products','max_pending_withdrawals'];
        foreach ($booleans as $key) {
            Setting::set('vendor', $key, $request->boolean("vendor.$key"));
        }
        foreach ($fields as $key) {
            if ($request->has("vendor.$key")) {
                Setting::set('vendor', $key, $request->input("vendor.$key"));
            }
        }
        Setting::set('vendor', 'payout_methods', $request->input('vendor.payout_methods', []));
        Cache::forget('settings.group.vendor');
        return response()->json(['message' => 'Vendor settings saved successfully.']);
    }

    public function affiliate()
    {
        $settings = Setting::group('affiliate');
        $tiers = \App\Models\AffiliateTier::ordered()->get();
        return view('content.settings.affiliate', compact('settings', 'tiers'));
    }

    public function updateAffiliate(Request $request)
    {
        $booleans = ['is_enabled','auto_approve','allow_self_referral','allow_l2_commissions'];
        $fields   = ['cookie_duration_days','hold_period_days','min_withdrawal','withdrawal_fee',
                     'default_commission_rate','default_l2_rate','commission_basis','terms_content'];

        foreach ($booleans as $key) {
            Setting::set('affiliate', $key, $request->boolean("affiliate.$key"));
        }
        foreach ($fields as $key) {
            if ($request->has("affiliate.$key")) {
                Setting::set('affiliate', $key, $request->input("affiliate.$key"));
            }
        }
        Setting::set('affiliate', 'payout_methods', $request->input('affiliate.payout_methods', ['wallet']));

        Cache::forget('settings.group.affiliate');
        return response()->json(['message' => 'Affiliate settings saved successfully.']);
    }

    public function refundEscrow()
    {
        $settings = Setting::group('refund_escrow');
        return view('content.settings.refund', compact('settings'));
    }

    public function updateRefundEscrow(Request $request)
    {
        $booleans = ['refund_enabled','partial_refund_enabled','refund_to_wallet_enabled','refund_to_original_enabled'];
        $numerics = ['auto_refund_window_hours','escrow_period_days','max_refund_percentage'];
        foreach ($booleans as $key) {
            Setting::set('refund_escrow', $key, $request->boolean("refund_escrow.$key"));
        }
        foreach ($numerics as $key) {
            if ($request->has("refund_escrow.$key")) {
                Setting::set('refund_escrow', $key, $request->input("refund_escrow.$key"));
            }
        }
        Cache::forget('settings.group.refund_escrow');
        return response()->json(['message' => 'Refund & escrow settings saved successfully.']);
    }

    public function reviews()
    {
        $settings = Setting::group('review');
        return view('content.settings.review', compact('settings'));
    }

    public function updateReviews(Request $request)
    {
        $booleans = ['reviews_enabled','auto_approve_reviews','require_purchase_for_review','allow_review_images','review_moderation_enabled','seller_can_reply'];
        $numerics = ['min_review_length','max_review_length'];
        foreach ($booleans as $key) {
            Setting::set('review', $key, $request->boolean("review.$key"));
        }
        foreach ($numerics as $key) {
            if ($request->has("review.$key")) {
                Setting::set('review', $key, $request->input("review.$key"));
            }
        }
        Cache::forget('settings.group.review');
        return response()->json(['message' => 'Review settings saved successfully.']);
    }

    /* ================================================================
     *  FINANCIAL
     * ================================================================ */

    public function wallet(CurrencyService $currencyService)
    {
        $walletSetting  = WalletSetting::global();
        $paymentMethods = PaymentMethod::where('is_enabled', true)->orderBy('sort_order')->get(['id','name','code','type']);
        return view('content.settings.wallet', compact('walletSetting', 'currencyService', 'paymentMethods'));
    }

    public function updateWallet(Request $request, CurrencyService $currencyService)
    {
        $walletSetting = WalletSetting::global();

        $data = $request->validate([
            'min_topup_amount'          => 'required|numeric|min:0',
            'max_topup_amount'          => 'nullable|numeric|gte:min_topup_amount',
            'max_daily_deposit_limit'   => 'nullable|numeric|min:0',
            'allowed_payment_gateways'   => 'nullable|array',
            'allowed_payment_gateways.*' => 'string|exists:payment_methods,code',
            'gateway_charge_type'   => 'required|in:percentage,fixed',
            'gateway_charge_amount' => 'required|numeric|min:0',
            'max_wallet_balance' => 'nullable|numeric|min:0',
            'min_transfer_amount'      => 'nullable|numeric|min:0',
            'max_transfer_amount'      => 'nullable|numeric|min:0',
            'max_daily_transfer_limit' => 'nullable|numeric|min:0',
            'transfer_charge_type'     => 'required|in:percentage,fixed',
            'transfer_charge_amount'   => 'required|numeric|min:0',
            'min_withdraw_amount'      => 'nullable|numeric|min:0',
            'max_withdraw_amount'      => 'nullable|numeric|min:0',
            'max_daily_withdraw_limit' => 'nullable|numeric|min:0',
            'withdraw_charge_type'     => 'required|in:percentage,fixed',
            'withdraw_charge_amount'   => 'required|numeric|min:0',
            'low_balance_threshold' => 'nullable|numeric|min:0',
        ]);

        $nullableToZero = ['max_topup_amount','max_daily_deposit_limit','max_wallet_balance',
            'min_transfer_amount','max_transfer_amount','max_daily_transfer_limit',
            'min_withdraw_amount','max_withdraw_amount','max_daily_withdraw_limit','low_balance_threshold'];
        foreach ($nullableToZero as $field) {
            $data[$field] = $data[$field] ?? 0;
        }

        $data['currency'] = $currencyService->code();
        $data += [
            'wallet_enabled'                 => $request->boolean('wallet_enabled'),
            'deposit_enabled'                => $request->boolean('deposit_enabled'),
            'partial_payment_enabled'        => $request->boolean('partial_payment_enabled'),
            'auto_deduct_wallet_for_partial' => $request->boolean('auto_deduct_wallet_for_partial'),
            'wallet_transfer_enabled'        => $request->boolean('wallet_transfer_enabled'),
            'withdraw_enabled'               => $request->boolean('withdraw_enabled'),
            'auto_approve_withdraw'          => $request->boolean('auto_approve_withdraw'),
            'low_balance_alert_enabled'      => $request->boolean('low_balance_alert_enabled'),
        ];

        $walletSetting->fill($data)->save();
        return response()->json(['message' => 'Wallet settings saved successfully.']);
    }

    public function invoice()
    {
        $settings = Setting::group('invoice');
        return view('content.settings.invoice', compact('settings'));
    }

    public function updateInvoice(Request $request)
    {
        Setting::set('invoice', 'auto_generate', $request->boolean('invoice.auto_generate'));
        foreach (['prefix','company_name','tax_number','company_address','footer_note'] as $key) {
            if ($request->has("invoice.$key")) {
                Setting::set('invoice', $key, $request->input("invoice.$key"));
            }
        }
        Cache::forget('settings.group.invoice');
        return response()->json(['message' => 'Invoice settings saved successfully.']);
    }

    public function currency()
    {
        $settings   = Setting::group('currency_locale');
        $currencies = Currency::where('is_active', true)->get();
        return view('content.settings.currency', compact('settings', 'currencies'));
    }

    public function updateCurrency(Request $request)
    {
        $booleans = ['rtl_enabled','multi_language_enabled'];
        $strings  = ['number_format','decimal_separator','thousands_separator','decimal_places','currency_position','default_language'];
        foreach ($booleans as $key) {
            Setting::set('currency_locale', $key, $request->boolean("currency_locale.$key"));
        }
        foreach ($strings as $key) {
            if ($request->has("currency_locale.$key")) {
                Setting::set('currency_locale', $key, $request->input("currency_locale.$key"));
            }
        }
        Cache::forget('settings.group.currency_locale');
        return response()->json(['message' => 'Currency & locale settings saved successfully.']);
    }

    /* ================================================================
     *  COMMUNICATION
     * ================================================================ */

    public function email()
    {
        $settings = Setting::group('email');
        return view('content.settings.email', compact('settings'));
    }

    public function updateEmail(Request $request)
    {
        $keys = ['mailer','host','port','encryption','timeout','username','password','from_address','from_name'];
        foreach ($keys as $key) {
            if ($request->has("email.$key")) {
                Setting::set('email', $key, $request->input("email.$key"));
            }
        }
        Cache::forget('settings.group.email');
        return response()->json(['message' => 'Email settings saved successfully.']);
    }

    public function notifications()
    {
        $generalSettings  = Setting::group('notifications');
        $orderSettings    = Setting::group('order_notifications');
        $ticketSettings   = Setting::group('ticket_notifications');
        $refundSettings   = Setting::group('refund_notifications');
        $walletSettings   = Setting::group('wallet_notifications');
        return view('content.settings.notifications', compact('generalSettings', 'orderSettings', 'ticketSettings', 'refundSettings', 'walletSettings'));
    }

    public function updateNotifications(Request $request, string $type)
    {
        $keyMap = [
            'general' => [
                'group' => 'notifications',
                'keys'  => ['admin_email','seller_approved','seller_rejected','seller_suspended','seller_reactivated',
                            'withdrawal_requested','withdrawal_approved','withdrawal_rejected',
                            'new_contact_message','new_product_review','product_request_status','subscriber_welcome'],
            ],
            'order' => [
                'group' => 'order_notifications',
                'keys'  => ['customer_on_placed','seller_on_placed','admin_on_placed','customer_on_paid','admin_on_paid',
                            'customer_on_status_change','customer_on_completed','seller_on_completed',
                            'customer_on_cancelled','seller_on_cancelled','admin_on_cancelled',
                            'customer_on_refunded','seller_on_refunded',
                            'customer_on_delivery','admin_on_delivery_failed'],
            ],
            'ticket' => [
                'group' => 'ticket_notifications',
                'keys'  => ['on_ticket_created','admin_on_new_ticket','on_staff_reply','on_customer_reply',
                            'on_status_change','on_ticket_closed','on_assigned','on_escalated'],
            ],
            'refund' => [
                'group' => 'refund_notifications',
                'keys'  => ['customer_on_requested','admin_on_requested','customer_on_approved',
                            'customer_on_rejected','customer_on_completed'],
            ],
            'wallet' => [
                'group' => 'wallet_notifications',
                'keys'  => ['on_deposit_confirmed','on_transfer_received','on_seller_transfer'],
            ],
        ];

        if (!isset($keyMap[$type])) {
            return response()->json(['message' => 'Invalid notification type.'], 422);
        }

        $group = $keyMap[$type]['group'];
        foreach ($keyMap[$type]['keys'] as $key) {
            if ($key === 'admin_email') {
                Setting::set($group, $key, $request->input($key, ''));
            } else {
                Setting::set($group, $key, $request->boolean($key));
            }
        }
        Cache::forget("settings.group.{$group}");
        return response()->json(['message' => ucfirst($type) . ' notification settings saved successfully.']);
    }

    /* ================================================================
     *  CONTENT & MARKETING
     * ================================================================ */

    public function seo()
    {
        $settings = Setting::group('seo');
        return view('content.settings.seo', compact('settings'));
    }

    public function updateSeo(Request $request)
    {
        $keys = ['meta_title','meta_description','meta_keywords','og_image','google_analytics','head_scripts'];
        foreach ($keys as $key) {
            if ($request->has("seo.$key")) {
                Setting::set('seo', $key, $request->input("seo.$key"));
            }
        }
        Cache::forget('settings.group.seo');
        return response()->json(['message' => 'SEO settings saved successfully.']);
    }

    public function social()
    {
        $settings = Setting::group('social');
        return view('content.settings.social', compact('settings'));
    }

    public function updateSocial(Request $request)
    {
        $keys = ['facebook','twitter','instagram','youtube','tiktok','linkedin','discord','telegram'];
        foreach ($keys as $key) {
            if ($request->has("social.$key")) {
                Setting::set('social', $key, $request->input("social.$key"));
            }
        }
        Cache::forget('settings.group.social');
        return response()->json(['message' => 'Social links saved successfully.']);
    }

    public function legal()
    {
        $settings = Setting::group('legal');
        return view('content.settings.legal', compact('settings'));
    }

    public function updateLegal(Request $request)
    {
        $keys = ['terms_of_service','privacy_policy','refund_policy','cookie_policy','seller_agreement'];
        foreach ($keys as $key) {
            if ($request->has("legal.$key")) {
                Setting::set('legal', $key, $request->input("legal.$key"));
            }
        }
        Cache::forget('settings.group.legal');
        return response()->json(['message' => 'Legal pages saved successfully.']);
    }

    public function website()
    {
        $homepage = Setting::group('homepage');
        $shoppage = Setting::group('shoppage');
        $footer   = Setting::group('footer');
        return view('content.settings.website', compact('homepage', 'shoppage', 'footer'));
    }

    public function updateWebsite(Request $request, string $section)
    {
        $handlers = [
            'homepage' => fn() => $this->saveHomepage($request),
            'shoppage' => fn() => $this->saveShoppage($request),
            'footer'   => fn() => $this->saveFooter($request),
        ];

        if (!isset($handlers[$section])) {
            return response()->json(['message' => 'Invalid section.'], 422);
        }

        $handlers[$section]();
        return response()->json(['message' => ucfirst($section) . ' settings saved successfully.']);
    }

    /* ================================================================
     *  SYSTEM
     * ================================================================ */

    public function apiIntegrations()
    {
        $settings = Setting::group('api_integration');
        return view('content.settings.api-integrations', compact('settings'));
    }

    public function updateApiIntegrations(Request $request)
    {
        $booleans = ['google_recaptcha_enabled', 'turnstile_enabled'];
        $strings  = ['api_rate_limit_per_minute','webhook_retry_count','webhook_retry_delay_seconds',
                     'captcha_provider',
                     'google_recaptcha_site_key','google_recaptcha_secret_key',
                     'turnstile_site_key','turnstile_secret_key',
                     'google_analytics_id',
                     'facebook_pixel_id','custom_head_scripts','custom_body_scripts'];
        foreach ($booleans as $key) {
            Setting::set('api_integration', $key, $request->boolean("api_integration.$key"));
        }
        foreach ($strings as $key) {
            if ($request->has("api_integration.$key")) {
                Setting::set('api_integration', $key, $request->input("api_integration.$key"));
            }
        }
        Cache::forget('settings.group.api_integration');
        return response()->json(['message' => 'API & integration settings saved successfully.']);
    }

    public function maintenance()
    {
        $settings = Setting::group('maintenance');
        return view('content.settings.maintenance', compact('settings'));
    }

    public function updateMaintenance(Request $request)
    {
        Setting::set('maintenance', 'enabled', $request->boolean('maintenance.enabled'));
        Setting::set('maintenance', 'message', $request->input('maintenance.message', ''));
        Setting::set('maintenance', 'allowed_ips', $request->input('maintenance.allowed_ips', ''));
        Setting::set('maintenance', 'expected_back', $request->input('maintenance.expected_back', ''));

        Cache::forget('settings.group.maintenance');
        return response()->json(['message' => 'Maintenance settings saved successfully.']);
    }

    public function ai()
    {
        $settings = Setting::group('ai');
        return view('content.settings.ai', compact('settings'));
    }

    public function updateAi(Request $request)
    {
        Setting::set('ai', 'enabled', $request->boolean('ai.enabled'));
        foreach (['provider','api_key','model'] as $key) {
            if ($request->has("ai.$key")) {
                Setting::set('ai', $key, $request->input("ai.$key"));
            }
        }
        Cache::forget('settings.group.ai');
        return response()->json(['message' => 'AI settings saved successfully.']);
    }

    /* ================================================================
     *  PRIVATE HELPERS
     * ================================================================ */

    protected function syncDefaultCurrency(string $currencyCode): void
    {
        Currency::where('is_default', true)->update(['is_default' => false]);
        Currency::where('code', $currencyCode)->where('is_active', true)->update(['is_default' => true]);
    }

    private function saveHomepage(Request $request): void
    {
        $sections = ['hero_slider','category_bar','featured_products','promotional_banner',
                     'new_arrivals','categories_grid','stats_counter','hot_deals',
                     'blog_section','newsletter','sections_order'];

        foreach ($sections as $section) {
            if ($request->has($section)) {
                $data = $request->input($section);
                if (is_string($data)) {
                    $decoded = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE) $data = $decoded;
                }
                Setting::set('homepage', $section, $data);
            }
        }

        if ($request->hasFile('promotional_banner_image')) {
            $path = $this->uploadFile($request->file('promotional_banner_image'), 'uploads/website');
            $banner = Setting::get('homepage', 'promotional_banner', []);
            $banner['image'] = $path;
            Setting::set('homepage', 'promotional_banner', $banner);
        }
        if ($request->hasFile('hot_deals_banner_image')) {
            $path = $this->uploadFile($request->file('hot_deals_banner_image'), 'uploads/website');
            $deals = Setting::get('homepage', 'hot_deals', []);
            $deals['banner_image'] = $path;
            Setting::set('homepage', 'hot_deals', $deals);
        }
        Cache::forget('settings.group.homepage');
    }

    private function saveShoppage(Request $request): void
    {
        foreach (['layout','filters','banner','seo'] as $section) {
            if ($request->has($section)) {
                $data = $request->input($section);
                if (is_string($data)) {
                    $decoded = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE) $data = $decoded;
                }
                Setting::set('shoppage', $section, $data);
            }
        }
        if ($request->hasFile('banner_image')) {
            $path = $this->uploadFile($request->file('banner_image'), 'uploads/website');
            $banner = Setting::get('shoppage', 'banner', []);
            $banner['image'] = $path;
            Setting::set('shoppage', 'banner', $banner);
        }
        Cache::forget('settings.group.shoppage');
    }

    private function saveFooter(Request $request): void
    {
        foreach (['about','columns','bottom_bar','payment_icons'] as $section) {
            if ($request->has($section)) {
                $data = $request->input($section);
                if (is_string($data)) {
                    $decoded = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE) $data = $decoded;
                }
                Setting::set('footer', $section, $data);
            }
        }
        Cache::forget('settings.group.footer');
    }

    private function uploadFile($file, string $folder): string
    {
        $dir = public_path($folder);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);
        return '/' . $folder . '/' . $filename;
    }
}
