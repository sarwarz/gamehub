<?php

namespace App\Services;

use App\Models\Tax;
use App\Models\User;
use App\Models\Order;
use App\Models\Currency;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\SellerOffer;
use App\Models\OrderAddress;
use App\Models\OrderDelivery;
use App\Models\SellerEarning;
use App\Models\PaymentMethod;
use App\Models\CheckoutSession;
use App\DTOs\WebhookPayload;
use App\Events\OrderPaid;
use App\PaymentGateways\GatewayFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    public function __construct(
        protected GatewayFactory $gatewayFactory,
        protected WalletService $walletService,
        protected CouponService $couponService,
    ) {}

    /* ================================================================
     |  Step 1: Create Checkout Session
     | ================================================================ */

    public function createSession(
        User    $user,
        array   $items,
        array   $billing,
        string  $currency,
        ?string $couponCode = null,
        ?string $idempotencyKey = null,
    ): CheckoutSession {

        $checkoutSettings = Setting::group('checkout');
        $storeSettings = Setting::group('store');

        $maxItems = (int) ($checkoutSettings['max_items_per_order'] ?? 50);
        if (count($items) > $maxItems) {
            throw new \RuntimeException("Maximum {$maxItems} items per order allowed.");
        }

        if ($idempotencyKey) {
            $existing = CheckoutSession::where('idempotency_key', $idempotencyKey)
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->where('expires_at', '>', now())
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        $subtotal      = 0;
        $sessionItems  = [];
        $productIds    = [];
        $categoryIds   = [];

        foreach ($items as $item) {
            $offer = SellerOffer::with('product.categories')
                ->where('id', $item['seller_offer_id'])
                ->where('status', 'active')
                ->first();

            if (!$offer) {
                throw new \RuntimeException("Seller offer #{$item['seller_offer_id']} is not available.");
            }

            $stock = $offer->keys()->where('status', 'available')->count();

            if ($stock < $item['quantity']) {
                $title = $offer->product->title ?? "Product";
                throw new \RuntimeException(
                    "Insufficient stock for \"{$title}\". Available: {$stock}, Requested: {$item['quantity']}"
                );
            }

            $unitPrice = $offer->resolveUnitPrice($item['quantity']);
            $lineTotal = bcmul($unitPrice, $item['quantity'], 2);
            $subtotal  = bcadd($subtotal, $lineTotal, 2);

            $productIds[] = $offer->product_id;
            $itemCategoryIds = [];
            if ($offer->product && $offer->product->categories) {
                foreach ($offer->product->categories as $cat) {
                    $categoryIds[] = $cat->id;
                    $itemCategoryIds[] = $cat->id;
                }
            }

            $sessionItems[] = [
                'seller_offer_id' => $offer->id,
                'seller_id'       => $offer->seller_id,
                'product_id'      => $offer->product_id,
                'quantity'        => $item['quantity'],
                'unit_price'      => (float) $unitPrice,
                'line_total'      => (float) $lineTotal,
                'product_title'   => $offer->product->title ?? '',
                'category_ids'    => $itemCategoryIds,
            ];
        }

        $taxAmount  = 0;
        $taxDetails = [];
        $billingCountry = $billing['country'] ?? null;
        $billingState   = $billing['state'] ?? null;
        $billingCity    = $billing['city'] ?? null;
        $taxEnabled     = $storeSettings['tax_enabled'] ?? true;

        if ($taxEnabled && $billingCountry) {
            $taxes = Tax::where('is_active', true)
                ->whereNull('seller_id')
                ->where('country', $billingCountry)
                ->when($billingState, fn ($q) => $q->where(function ($q) use ($billingState) {
                    $q->whereNull('state')->orWhere('state', '')->orWhere('state', $billingState);
                }))
                ->when($billingCity, fn ($q) => $q->where(function ($q) use ($billingCity) {
                    $q->whereNull('city')->orWhere('city', '')->orWhere('city', $billingCity);
                }))
                ->orderBy('priority')
                ->get();

            foreach ($taxes as $tax) {
                $base   = $tax->is_compound ? bcadd($subtotal, $taxAmount, 2) : $subtotal;
                $amount = $tax->type === 'percent'
                    ? round($base * $tax->rate / 100, 2)
                    : $tax->rate;

                $taxAmount    = bcadd($taxAmount, $amount, 2);
                $taxDetails[] = [
                    'name'   => $tax->name,
                    'rate'   => $tax->rate,
                    'type'   => $tax->type,
                    'amount' => $amount,
                ];
            }
        }

        $discountAmount = 0;
        $couponData     = null;
        $couponId       = null;

        if ($couponCode) {
            $couponItems = array_map(function ($si) {
                return [
                    'product_id'      => $si['product_id'],
                    'seller_offer_id' => $si['seller_offer_id'],
                    'quantity'        => $si['quantity'],
                    'unit_price'      => $si['unit_price'],
                    'line_total'      => $si['line_total'],
                    'category_ids'    => $si['category_ids'] ?? [],
                ];
            }, $sessionItems);

            $result = $this->couponService->validate($couponCode, $subtotal, $couponItems, $user->id);

            if (!$result['valid']) {
                throw new \RuntimeException($result['error']);
            }

            $discountAmount = $result['discount'];
            $this->couponService->incrementUsage($result['coupon']);

            $couponId   = $result['coupon']->id;
            $couponData = $result['coupon_data'];
        }

        $baseTotalAmount = max(0, bcadd(bcsub($subtotal, $discountAmount, 2), $taxAmount, 2));

        $minOrderAmount = (float) ($storeSettings['min_order_amount'] ?? 0);
        if ($minOrderAmount > 0 && $baseTotalAmount < $minOrderAmount) {
            throw new \RuntimeException("Minimum order amount is {$minOrderAmount}.");
        }

        // Resolve exchange rate: convert from base currency to customer's currency
        $defaultCurrency = Currency::where('is_default', true)->first();
        $baseCurrencyCode = $defaultCurrency->code ?? 'USD';
        $exchangeRate = 1.00000000;

        if (strtoupper($currency) !== strtoupper($baseCurrencyCode)) {
            $targetCurrency = Currency::where('code', strtoupper($currency))
                ->where('is_active', true)
                ->first();

            if (!$targetCurrency) {
                throw new \RuntimeException("Currency {$currency} is not supported.");
            }

            $exchangeRate = (float) $targetCurrency->rate;
        }

        // Base amounts (platform's internal currency)
        $baseSubtotal        = $subtotal;
        $baseTaxAmount       = $taxAmount;
        $baseDiscountAmount  = $discountAmount;

        // Converted amounts (customer's currency)
        $convertedSubtotal       = round($baseSubtotal * $exchangeRate, 2);
        $convertedTaxAmount      = round($baseTaxAmount * $exchangeRate, 2);
        $convertedDiscountAmount = round($baseDiscountAmount * $exchangeRate, 2);
        $convertedTotalAmount    = max(0, round($baseTotalAmount * $exchangeRate, 2));

        // Convert session item prices for gateway accuracy
        $convertedItems = array_map(function ($item) use ($exchangeRate) {
            $item['base_unit_price'] = $item['unit_price'];
            $item['base_line_total'] = $item['line_total'];
            $item['unit_price']      = round($item['unit_price'] * $exchangeRate, 2);
            $item['line_total']      = round($item['line_total'] * $exchangeRate, 2);
            return $item;
        }, $sessionItems);

        $sessionTimeoutMinutes = (int) ($checkoutSettings['session_timeout_minutes'] ?? 30);

        return CheckoutSession::create([
            'uuid'                 => Str::uuid()->toString(),
            'user_id'              => $user->id,
            'idempotency_key'      => $idempotencyKey,
            'items'                => $convertedItems,
            'billing'              => $billing,
            'currency'             => strtoupper($currency),
            'base_currency'        => $baseCurrencyCode,
            'base_subtotal'        => $baseSubtotal,
            'base_tax_amount'      => $baseTaxAmount,
            'base_discount_amount' => $baseDiscountAmount,
            'base_total_amount'    => $baseTotalAmount,
            'exchange_rate'        => $exchangeRate,
            'subtotal'             => $convertedSubtotal,
            'tax_amount'           => $convertedTaxAmount,
            'discount_amount'      => $convertedDiscountAmount,
            'total_amount'         => $convertedTotalAmount,
            'wallet_amount'        => 0,
            'gateway_amount'       => $convertedTotalAmount,
            'coupon_id'            => $couponId,
            'coupon_data'          => $couponData,
            'tax_data'             => $taxDetails ?: null,
            'trx'                  => 'TRX-' . now()->format('ymd') . '-' . strtoupper(Str::random(6)),
            'status'               => 'open',
            'expires_at'           => now()->addMinutes($sessionTimeoutMinutes),
        ]);
    }

    /* ================================================================
     |  Step 2: Initiate Payment
     | ================================================================ */

    public function initiatePayment(
        CheckoutSession $session,
        string          $paymentMethod,
        bool            $useWallet = false,
    ): array {

        if ($session->isExpired()) {
            throw new \RuntimeException('Checkout session has expired. Please start a new checkout.');
        }

        if (!$session->isOpen()) {
            throw new \RuntimeException('This checkout session is no longer available.');
        }

        $method = PaymentMethod::where('code', $paymentMethod)
            ->where('is_enabled', true)
            ->first();

        if (!$method && $paymentMethod !== 'wallet') {
            throw new \RuntimeException('Selected payment method is not available.');
        }

        foreach ($session->items as $item) {
            $stock = SellerOffer::find($item['seller_offer_id'])
                ?->keys()
                ->where('status', 'available')
                ->count() ?? 0;

            if ($stock < $item['quantity']) {
                throw new \RuntimeException(
                    "Stock no longer available for \"{$item['product_title']}\". Please start a new checkout."
                );
            }
        }

        $keyService = app(KeyReservationService::class);
        $reservedIds = $keyService->reserve($session, $session->items);

        $walletAmount  = 0;
        $gatewayAmount = (float) $session->total_amount;
        $isFullWallet  = false;
        $user          = $session->user;
        $meta          = $session->meta ?? [];

        if ($paymentMethod === 'wallet' || $useWallet) {
            $wallet = $this->walletService->getOrCreateWallet($user);
            $this->walletService->ensureWalletUsable($wallet);

            if ($paymentMethod === 'wallet') {
                if ($wallet->balance < $session->total_amount) {
                    $keyService->release($session);
                    throw new \RuntimeException('Insufficient wallet balance.');
                }

                $walletAmount  = (float) $session->total_amount;
                $gatewayAmount = 0;
                $isFullWallet  = true;
            } else {
                $settings = $this->walletService->settings();
                if (!$settings->partial_payment_enabled) {
                    $keyService->release($session);
                    throw new \RuntimeException('Partial wallet payment is not enabled.');
                }

                $walletAmount  = min((float) $wallet->balance, (float) $session->total_amount);
                $gatewayAmount = bcsub($session->total_amount, $walletAmount, 2);
            }

            if ($walletAmount > 0) {
                $walletTrx = $this->walletService->debit(
                    wallet: $wallet,
                    amount: $walletAmount,
                    source: 'order',
                    description: "Payment hold for checkout {$session->trx}",
                    status: 'pending',
                );
                $meta['wallet_transaction_id'] = $walletTrx->id;
            }
        }

        $session->update([
            'payment_method'  => $paymentMethod,
            'wallet_amount'   => $walletAmount,
            'gateway_amount'  => $gatewayAmount,
            'reserved_key_ids' => $reservedIds,
            'meta'            => array_merge($meta, [
                'wallet' => [
                    'wallet_amount'   => $walletAmount,
                    'gateway_amount'  => $gatewayAmount,
                    'full_wallet_pay' => $isFullWallet,
                ],
            ]),
            'status' => 'paying',
        ]);

        if ($isFullWallet) {
            return $this->fulfillSession($session);
        }

        $gateway = $this->gatewayFactory->make($paymentMethod);
        $result  = $gateway->createPayment($session, $method->config ?? []);

        $session->update(['gateway_reference' => $result->gatewayReference]);

        return [
            'session_uuid'    => $session->uuid,
            'gateway'         => $paymentMethod,
            'payment'         => $result->toFrontend(),
            'wallet_applied'  => $walletAmount,
            'gateway_amount'  => $gatewayAmount,
        ];
    }

    /* ================================================================
     |  Step 3: Fulfill Session (called by webhook or wallet-pay)
     | ================================================================ */

    public function fulfillSession(CheckoutSession $session, ?WebhookPayload $payload = null): array
    {
        if ($session->isCompleted()) {
            $order = Order::where('meta->checkout_session_id', $session->id)->first();
            return $this->buildResult($session, $order);
        }

        $order = DB::transaction(function () use ($session, $payload) {

            $session = CheckoutSession::lockForUpdate()->find($session->id);

            if ($session->status === 'completed') {
                return Order::where('meta->checkout_session_id', $session->id)->first();
            }

            $user = $session->user;

            if ($payload && $payload->amount !== null && $session->gateway_amount > 0) {
                $diff = abs($payload->amount - (float) $session->gateway_amount);
                if ($diff > 0.02) {
                    Log::error('Payment amount mismatch', [
                        'expected' => $session->gateway_amount,
                        'received' => $payload->amount,
                        'trx'      => $session->trx,
                    ]);
                    throw new \RuntimeException('Payment amount mismatch.');
                }
            }

            $order = Order::create([
                'user_id'              => $user->id,
                'currency'             => $session->currency,
                'base_currency'        => $session->base_currency,
                'base_subtotal'        => $session->base_subtotal,
                'base_tax_amount'      => $session->base_tax_amount,
                'base_discount_amount' => $session->base_discount_amount,
                'base_total_amount'    => $session->base_total_amount,
                'exchange_rate'        => $session->exchange_rate,
                'subtotal'             => $session->subtotal,
                'tax_amount'           => $session->tax_amount,
                'discount_amount'      => $session->discount_amount,
                'total_amount'         => $session->total_amount,
                'payment_method'       => $session->payment_method,
                'payment_reference'    => $payload?->gatewayReference ?? $session->gateway_reference,
                'payment_status'       => 'paid',
                'status'               => 'processing',
                'paid_at'              => now(),
                'meta'                 => [
                    'checkout_session_id' => $session->id,
                    'client'    => $session->meta['client'] ?? [],
                    'checkout'  => ['source' => 'api', 'version' => 'v2'],
                    'flags'     => ['auto_delivery' => true],
                    'tax_details' => $session->tax_data,
                    'coupon'      => $session->coupon_data,
                    'wallet'      => $session->meta['wallet'] ?? null,
                ],
            ]);

            OrderAddress::create([
                'order_id' => $order->id,
                'type'     => 'billing',
                ...$session->billing,
            ]);

            foreach ($session->items as $itemData) {
                // Seller earnings are always in base currency for consistent accounting
                $baseLineTotal = (float) ($itemData['base_line_total'] ?? $itemData['line_total']);
                $commissionRate = SellerBalanceService::getCommissionRate(
                    SellerOffer::find($itemData['seller_offer_id'])->product
                );
                $commission = round($baseLineTotal * ($commissionRate / 100), 2);
                $netAmount  = bcsub($baseLineTotal, $commission, 2);

                $orderItem = OrderItem::create([
                    'order_id'        => $order->id,
                    'seller_id'       => $itemData['seller_id'],
                    'product_id'      => $itemData['product_id'],
                    'seller_offer_id' => $itemData['seller_offer_id'],
                    'quantity'        => $itemData['quantity'],
                    'unit_price'      => $itemData['unit_price'],
                    'subtotal'        => $itemData['line_total'],
                    'delivery_type'   => 'auto',
                    'delivery_status' => 'pending',
                    'status'          => 'active',
                ]);

                OrderDelivery::create([
                    'order_item_id'   => $orderItem->id,
                    'delivery_method' => 'auto',
                    'status'          => 'pending',
                ]);

                SellerEarning::create([
                    'seller_id'     => $itemData['seller_id'],
                    'order_id'      => $order->id,
                    'order_item_id' => $orderItem->id,
                    'gross_amount'  => $baseLineTotal,
                    'commission'    => $commission,
                    'net_amount'    => $netAmount,
                    'status'        => 'pending',
                ]);
            }

            Transaction::create([
                'user_id'        => $user->id,
                'reference_type' => Order::class,
                'reference_id'   => $order->id,
                'trx'            => $session->trx,
                'amount'         => $session->total_amount,
                'fee'            => 0,
                'net_amount'     => $session->total_amount,
                'currency'       => $session->currency,
                'type'           => 'debit',
                'category'       => 'order',
                'status'         => 'completed',
                'payment_method' => $session->payment_method,
                'gateway'        => $session->payment_method,
                'meta'           => $payload?->raw ?? [],
            ]);

            if (($session->meta['wallet_transaction_id'] ?? null)) {
                $this->walletService->confirmWalletHold(
                    (int) $session->meta['wallet_transaction_id'],
                    $order->id,
                );
            }

            $keyService = app(KeyReservationService::class);
            $keyService->assign($session, $order);

            $session->update([
                'status'  => 'completed',
                'paid_at' => now(),
            ]);

            $order->notes()->create([
                'user_id' => $user->id,
                'note'    => "Payment completed via {$session->payment_method}. Transaction: {$session->trx}",
                'type'    => 'system',
                'is_visible_to_customer' => true,
            ]);

            return $order;
        });

        OrderNotificationService::orderPlaced($order);
        event(new OrderPaid($order));

        return $this->buildResult($session->fresh(), $order);
    }

    /* ================================================================
     |  Step 4: Get Result
     | ================================================================ */

    public function getResult(string $uuid, int $userId): array
    {
        $session = CheckoutSession::where('uuid', $uuid)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($session->isCompleted()) {
            $order = Order::where('meta->checkout_session_id', $session->id)->first();
            return $this->buildResult($session, $order);
        }

        return [
            'status'      => $session->status,
            'session_uuid' => $session->uuid,
            'expires_at'  => $session->expires_at->toISOString(),
        ];
    }

    /* ================================================================
     |  Expire Session
     | ================================================================ */

    public function expireSession(CheckoutSession $session): void
    {
        if ($session->status === 'completed') {
            return;
        }

        DB::transaction(function () use ($session) {
            $session = CheckoutSession::lockForUpdate()->find($session->id);

            if ($session->status === 'completed') {
                return;
            }

            $keyService = app(KeyReservationService::class);
            $keyService->release($session);

            if ($session->coupon_id) {
                $this->couponService->decrementUsage($session->coupon_id);
            }

            if ($session->meta['wallet_transaction_id'] ?? null) {
                try {
                    $this->walletService->failDeposit(
                        (int) $session->meta['wallet_transaction_id']
                    );
                } catch (\Throwable $e) {
                    Log::warning('Failed to revert wallet hold on session expire', [
                        'session_id' => $session->id,
                        'error'      => $e->getMessage(),
                    ]);
                }
            }

            $session->update(['status' => 'expired']);
        });
    }

    /* ================================================================
     |  Helpers
     | ================================================================ */

    protected function buildResult(CheckoutSession $session, ?Order $order): array
    {
        $data = [
            'status'       => 'completed',
            'session_uuid' => $session->uuid,
        ];

        if ($order) {
            $data['order'] = [
                'id'              => $order->id,
                'order_number'    => $order->order_number,
                'status'          => $order->status,
                'payment_status'  => $order->payment_status,
                'currency'        => $order->currency,
                'subtotal'        => (float) $order->subtotal,
                'tax_amount'      => (float) $order->tax_amount,
                'discount_amount' => (float) $order->discount_amount,
                'total_amount'    => (float) $order->total_amount,
                'base_currency'   => $order->base_currency,
                'base_total_amount' => (float) ($order->base_total_amount ?? $order->total_amount),
                'exchange_rate'   => (float) ($order->exchange_rate ?? 1),
                'paid_at'         => $order->paid_at?->toISOString(),
            ];
        }

        return $data;
    }
}
