<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Currency;
use App\Models\WalletSetting;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;

/**
 * @group Settings
 *
 * Public platform configuration endpoints.
 * These endpoints expose non-sensitive settings that frontend/mobile apps
 * need to render the storefront correctly.
 *
 * @unauthenticated
 */
class SettingController extends Controller
{
    /**
     * Groups and keys that are safe to expose publicly.
     * null = all keys in that group are public.
     */
    private function publicGroups(): array
    {
        return [
            'general'         => ['site_name', 'tagline', 'currency', 'timezone', 'date_format', 'per_page'],
            'branding'        => null,
            'store'           => ['order_prefix', 'min_order_amount', 'guest_checkout', 'tax_enabled', 'tax_display', 'show_out_of_stock'],
            'vendor'          => ['registration_enabled', 'commission_type'],
            'seo'             => ['meta_title', 'meta_description', 'meta_keywords', 'og_image'],
            'social'          => null,
            'legal'           => null,
            'maintenance'     => ['enabled', 'message', 'expected_back'],
            'checkout'        => ['session_timeout_minutes', 'max_items_per_order', 'guest_checkout_enabled', 'require_billing_address'],
            'security'        => ['two_factor_enabled', 'password_min_length', 'password_require_uppercase', 'password_require_number', 'password_require_symbol'],
            'registration'    => ['require_email_verification', 'allow_social_login', 'social_providers', 'registration_enabled'],
            'review'          => ['reviews_enabled', 'require_purchase_for_review', 'allow_review_images', 'seller_can_reply', 'min_review_length', 'max_review_length'],
            'refund_escrow'   => ['refund_enabled', 'escrow_period_days', 'partial_refund_enabled', 'auto_refund_window_hours', 'max_refund_percentage'],
            'currency_locale' => ['number_format', 'decimal_separator', 'thousands_separator', 'decimal_places', 'currency_position', 'rtl_enabled', 'default_language'],
            'api_integration' => ['captcha_provider', 'google_analytics_id', 'facebook_pixel_id'],
            'product'         => ['max_images_per_product', 'allow_product_requests'],
        ];
    }

    /**
     * Type cast map for settings keys.
     * Keys not listed here remain as strings.
     */
    private function castMap(): array
    {
        return [
            'boolean' => [
                'guest_checkout', 'tax_enabled', 'show_out_of_stock',
                'registration_enabled',
                'enabled',
                'guest_checkout_enabled', 'require_billing_address',
                'two_factor_enabled', 'password_require_uppercase', 'password_require_number', 'password_require_symbol',
                'require_email_verification', 'allow_social_login',
                'reviews_enabled', 'require_purchase_for_review', 'allow_review_images', 'seller_can_reply',
                'refund_enabled', 'partial_refund_enabled',
                'rtl_enabled',
                'allow_product_requests',
            ],
            'integer' => [
                'per_page',
                'session_timeout_minutes', 'max_items_per_order',
                'password_min_length',
                'min_review_length', 'max_review_length',
                'escrow_period_days', 'auto_refund_window_hours', 'max_refund_percentage',
                'decimal_places',
                'max_images_per_product',
            ],
            'float' => [
                'min_order_amount',
            ],
            'json' => [
                'social_providers',
            ],
        ];
    }

    /**
     * Apply type casting to a key-value settings collection.
     */
    private function castValues(array $settings): array
    {
        $casts = $this->castMap();
        $booleans = array_flip($casts['boolean']);
        $integers = array_flip($casts['integer']);
        $floats   = array_flip($casts['float']);
        $jsons    = array_flip($casts['json'] ?? []);

        foreach ($settings as $key => &$value) {
            if (isset($booleans[$key])) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            } elseif (isset($integers[$key])) {
                $value = (int) $value;
            } elseif (isset($floats[$key])) {
                $value = (float) $value;
            } elseif (isset($jsons[$key]) && is_string($value)) {
                $value = json_decode($value, true) ?? [];
            }
        }

        return $settings;
    }

    /**
     * Fetch and filter all public settings with proper type casting.
     */
    private function fetchPublicSettings(): array
    {
        $publicGroups = $this->publicGroups();

        $settings = Setting::whereIn('group', array_keys($publicGroups))
            ->get()
            ->groupBy('group')
            ->map(function ($items, $group) use ($publicGroups) {
                $allowedKeys = $publicGroups[$group];
                if ($allowedKeys !== null) {
                    $items = $items->whereIn('key', $allowedKeys);
                }
                return $items->pluck('value', 'key')->toArray();
            })
            ->toArray();

        foreach ($settings as $group => &$values) {
            $values = $this->castValues($values);
        }

        $captchaProvider = $settings['api_integration']['captcha_provider'] ?? 'none';
        $settings['captcha'] = $this->buildCaptchaConfig($captchaProvider);
        unset($settings['api_integration']['captcha_provider']);

        return $settings;
    }

    /**
     * Build a clean captcha config with only the relevant site key.
     */
    private function buildCaptchaConfig(string $provider): array
    {
        $config = ['provider' => $provider, 'site_key' => null];

        if ($provider === 'recaptcha') {
            $config['site_key'] = Setting::get('api_integration', 'google_recaptcha_site_key', '');
        } elseif ($provider === 'turnstile') {
            $config['site_key'] = Setting::get('api_integration', 'turnstile_site_key', '');
        }

        return $config;
    }

    private function fetchCurrencies()
    {
        return Currency::where('is_active', true)
            ->select('code', 'name', 'symbol', 'rate', 'is_default')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all public settings
     *
     * Returns all publicly safe platform settings grouped by category.
     * Sensitive settings (SMTP credentials, secret keys, admin emails, etc.) are excluded.
     * All values are properly typed (booleans, integers, floats).
     *
     * @response 200 scenario="Success" {"status":true,"message":"Settings fetched successfully","data":{"general":{"site_name":"GameHub","per_page":12},"store":{"min_order_amount":0.0,"tax_enabled":true},"captcha":{"provider":"none","site_key":null},"currencies":[{"code":"USD","name":"US Dollar","symbol":"$","rate":"1.00000000","is_default":true}]}}
     */
    public function index(): JsonResponse
    {
        try {
            $settings = $this->fetchPublicSettings();
            $settings['currencies'] = $this->fetchCurrencies();

            return $this->success($settings, 'Settings fetched successfully')
                ->header('Cache-Control', 'public, max-age=300, s-maxage=600');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch settings', 500);
        }
    }

    /**
     * Bootstrap settings
     *
     * Returns everything the frontend needs on initial load in a single call:
     * all public settings, active currencies, wallet configuration, default currency,
     * and a dedicated captcha config. Designed to minimize network requests on app startup.
     *
     * @response 200 scenario="Success" {"status":true,"message":"Bootstrap data fetched successfully","data":{"settings":{"general":{"site_name":"GameHub","per_page":12},"captcha":{"provider":"none","site_key":null}},"currencies":[],"default_currency":{"code":"USD","symbol":"$"},"wallet":{"enabled":true,"deposit_enabled":true}}}
     */
    public function bootstrap(): JsonResponse
    {
        try {
            $settings = $this->fetchPublicSettings();
            $currencies = $this->fetchCurrencies();

            $walletSetting = WalletSetting::global();
            $wallet = [
                'enabled'                 => (bool) $walletSetting->wallet_enabled,
                'deposit_enabled'         => (bool) $walletSetting->deposit_enabled,
                'min_topup_amount'        => (float) $walletSetting->min_topup_amount,
                'max_topup_amount'        => (float) $walletSetting->max_topup_amount,
                'partial_payment_enabled' => (bool) $walletSetting->partial_payment_enabled,
                'wallet_transfer_enabled' => (bool) $walletSetting->wallet_transfer_enabled,
            ];

            $currencyService = app(CurrencyService::class);

            return $this->success([
                'settings'         => $settings,
                'currencies'       => $currencies,
                'default_currency' => [
                    'code'   => $currencyService->code(),
                    'symbol' => $currencyService->symbol(),
                ],
                'locale'           => $currencyService->localeConfig(),
                'wallet'           => $wallet,
            ], 'Bootstrap data fetched successfully')
                ->header('Cache-Control', 'public, max-age=300, s-maxage=600');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch bootstrap data', 500);
        }
    }

    /**
     * Get settings by group
     *
     * Returns publicly safe settings for a specific group with proper type casting.
     *
     * @urlParam group string required The settings group name. Example: general
     *
     * @response 200 scenario="Success" {"status":true,"message":"Settings group fetched successfully","data":{"site_name":"GameHub","per_page":12}}
     * @response 404 scenario="Not found" {"status":false,"message":"Settings group not found or not public."}
     */
    public function show(string $group): JsonResponse
    {
        try {
            $publicGroups = $this->publicGroups();

            if ($group === 'captcha') {
                $provider = Setting::get('api_integration', 'captcha_provider', 'none');
                return $this->success(
                    $this->buildCaptchaConfig($provider),
                    'Captcha settings fetched successfully'
                )->header('Cache-Control', 'public, max-age=300, s-maxage=600');
            }

            if (!array_key_exists($group, $publicGroups)) {
                return $this->error('Settings group not found or not public.', 404);
            }

            $query = Setting::where('group', $group);

            $allowedKeys = $publicGroups[$group];
            if ($allowedKeys !== null) {
                $query->whereIn('key', $allowedKeys);
            }

            $settings = $query->pluck('value', 'key')->toArray();
            $settings = $this->castValues($settings);

            return $this->success($settings, 'Settings group fetched successfully')
                ->header('Cache-Control', 'public, max-age=300, s-maxage=600');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch settings group', 500);
        }
    }

    /**
     * Get active currencies
     *
     * Returns all active currencies with exchange rates.
     *
     * @response 200 scenario="Success" {"status":true,"message":"Currencies fetched successfully","data":[{"code":"USD","name":"US Dollar","symbol":"$","rate":"1.00000000","is_default":true}]}
     */
    public function currencies(): JsonResponse
    {
        try {
            return $this->success(
                $this->fetchCurrencies(),
                'Currencies fetched successfully'
            )->header('Cache-Control', 'public, max-age=300, s-maxage=600');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch currencies', 500);
        }
    }
}
