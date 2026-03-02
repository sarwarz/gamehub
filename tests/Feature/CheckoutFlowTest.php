<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Seller;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ProductCategory;
use App\Models\SellerOffer;
use App\Models\SellerOfferKey;
use App\Models\SellerBalance;
use App\Models\SellerEarning;
use App\Models\Wallet;
use App\Models\WalletSetting;
use App\Models\WalletTransaction;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderDelivery;
use App\Models\OrderAddress;
use App\Models\OrderNote;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use App\Models\CheckoutSession;
use App\Models\Setting;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderPaymentNotification;
use App\Notifications\OrderStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $buyer;
    protected User $sellerUser;
    protected Seller $seller;
    protected Product $product;
    protected SellerOffer $offer;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // SQLite compat: override Order boot to avoid CAST AS UNSIGNED
        Order::creating(function ($order) {
            if (!$order->order_number) {
                $max = DB::table('orders')->max('order_number');
                $next = $max ? ((int) $max + 1) : 1;
                $order->order_number = str_pad($next, 6, '0', STR_PAD_LEFT);
            }
        });

        $this->seedTestData();
    }

    protected function seedTestData(): void
    {
        // Wallet settings
        WalletSetting::create([
            'wallet_enabled'          => true,
            'deposit_enabled'         => true,
            'min_topup_amount'        => 1,
            'max_topup_amount'        => 10000,
            'partial_payment_enabled' => true,
            'max_wallet_balance'      => 99999,
            'wallet_transfer_enabled' => false,
            'withdraw_enabled'        => false,
            'low_balance_alert_enabled' => false,
        ]);

        // Order notification settings
        Setting::set('order_notifications', 'customer_on_placed', true);
        Setting::set('order_notifications', 'seller_on_placed', true);
        Setting::set('order_notifications', 'admin_on_placed', true);
        Setting::set('order_notifications', 'customer_on_paid', true);
        Setting::set('order_notifications', 'admin_on_paid', true);
        Setting::set('order_notifications', 'customer_on_status_change', true);
        Setting::set('order_notifications', 'customer_on_completed', true);
        Setting::set('order_notifications', 'seller_on_completed', true);
        Setting::set('order_notifications', 'customer_on_cancelled', true);
        Setting::set('order_notifications', 'seller_on_cancelled', true);
        Setting::set('order_notifications', 'admin_on_cancelled', true);

        // Payment method (wallet)
        PaymentMethod::create([
            'name'       => 'Wallet',
            'code'       => 'wallet',
            'type'       => 'wallet',
            'is_enabled' => true,
            'mode'       => 'live',
            'config'     => [],
        ]);

        // Buyer
        $this->buyer = User::factory()->create([
            'name'       => 'Test Buyer',
            'username'   => 'testbuyer',
            'email'      => 'buyer@test.com',
            'is_active'  => true,
            'is_verified' => true,
        ]);

        $this->token = $this->buyer->createToken('test')->plainTextToken;

        // Seller user
        $this->sellerUser = User::factory()->create([
            'name'       => 'Test Seller',
            'username'   => 'testseller',
            'email'      => 'seller@test.com',
            'is_active'  => true,
            'is_verified' => true,
        ]);

        $this->seller = Seller::create([
            'user_id'     => $this->sellerUser->id,
            'store_name'  => 'Test Game Store',
            'slug'        => 'test-game-store',
            'email'       => 'seller@test.com',
            'country'     => 'US',
            'city'        => 'New York',
            'address'     => '123 Seller St',
            'status'      => 'active',
            'is_verified' => true,
            'rating'      => 4.5,
            'total_sales' => 0,
        ]);

        // Product type with commission
        $productType = ProductType::create([
            'name'       => 'Game Key',
            'slug'       => 'game-key',
            'status'     => 'active',
            'commission' => 10.00,
        ]);

        // Product
        $this->product = Product::create([
            'title'             => 'GTA V Steam Key',
            'slug'              => 'gta-v-steam-key',
            'sku'               => 'GTA5-STEAM-001',
            'short_description' => 'Grand Theft Auto V for Steam',
            'description'       => 'Full game key for GTA V on Steam platform.',
            'image'             => 'products/gta5.jpg',
            'delivery_type'     => 'auto',
            'status'            => 'active',
        ]);

        $this->product->types()->attach($productType->id);

        // Seller offer
        $this->offer = SellerOffer::create([
            'seller_id'    => $this->seller->id,
            'product_id'   => $this->product->id,
            'retail_price' => 29.99,
            'sale_mode'    => 'retail',
            'status'       => 'active',
            'is_verified'  => true,
        ]);

        // Product keys (stock)
        for ($i = 1; $i <= 5; $i++) {
            SellerOfferKey::create([
                'seller_offer_id' => $this->offer->id,
                'type'            => 'text',
                'value'           => "GAME-KEY-TEST-{$i}",
                'status'          => 'available',
            ]);
        }
    }

    protected function creditBuyerWallet(float $amount): void
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $this->buyer->id],
            ['balance' => 0, 'is_active' => true]
        );

        $wallet->update(['balance' => bcadd($wallet->balance, $amount, 2)]);

        WalletTransaction::create([
            'wallet_id'   => $wallet->id,
            'amount'      => $amount,
            'type'        => 'credit',
            'source'      => 'deposit',
            'description' => 'Test wallet credit',
            'status'      => 'completed',
            'balance_after' => $wallet->fresh()->balance,
        ]);
    }

    /* ================================================================
     |  TEST: Full Wallet Checkout Flow
     | ================================================================ */

    public function test_full_wallet_checkout_flow(): void
    {
        Notification::fake();

        // 1. Credit wallet with $100
        $this->creditBuyerWallet(100.00);

        $wallet = Wallet::where('user_id', $this->buyer->id)->first();
        $this->assertEquals('100.00', $wallet->balance);

        // 2. Create checkout session
        $sessionResponse = $this->withHeaders([
            'Authorization'    => "Bearer {$this->token}",
            'X-Idempotency-Key' => 'test-idempotency-key-001',
        ])->postJson('/api/v1/checkout/sessions', [
            'items' => [
                ['seller_offer_id' => $this->offer->id, 'quantity' => 2],
            ],
            'billing' => [
                'name'    => 'Test Buyer',
                'email'   => 'buyer@test.com',
                'phone'   => '+1234567890',
                'address' => '456 Buyer Ave',
                'city'    => 'Los Angeles',
                'state'   => 'CA',
                'country' => 'US',
                'postcode' => '90001',
            ],
            'currency' => 'USD',
        ]);

        $sessionResponse->assertStatus(201);
        $sessionData = $sessionResponse->json('data');

        $this->assertNotEmpty($sessionData['session_uuid']);
        $this->assertEquals(59.98, $sessionData['total_amount']);
        $this->assertEquals(29.99, $sessionData['items'][0]['unit_price']);
        $this->assertEquals(2, $sessionData['items'][0]['quantity']);
        $this->assertEquals('USD', $sessionData['currency']);

        $sessionUuid = $sessionData['session_uuid'];

        // Verify session stored in DB
        $session = CheckoutSession::where('uuid', $sessionUuid)->first();
        $this->assertNotNull($session);
        $this->assertEquals('open', $session->status);
        $this->assertEquals($this->buyer->id, $session->user_id);

        // 3. Idempotency: same key returns same session
        $idempotentResponse = $this->withHeaders([
            'Authorization'    => "Bearer {$this->token}",
            'X-Idempotency-Key' => 'test-idempotency-key-001',
        ])->postJson('/api/v1/checkout/sessions', [
            'items' => [
                ['seller_offer_id' => $this->offer->id, 'quantity' => 2],
            ],
            'billing' => [
                'name' => 'Test Buyer', 'email' => 'buyer@test.com',
                'address' => '456 Buyer Ave', 'city' => 'Los Angeles',
                'country' => 'US',
            ],
            'currency' => 'USD',
        ]);

        $idempotentResponse->assertStatus(201);
        $this->assertEquals($sessionUuid, $idempotentResponse->json('data.session_uuid'));

        // 4. Pay with wallet (full balance)
        $payResponse = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson("/api/v1/checkout/sessions/{$sessionUuid}/pay", [
            'payment_method' => 'wallet',
        ]);

        $payResponse->assertStatus(200);
        $payData = $payResponse->json('data');

        $this->assertEquals('completed', $payData['status']);
        $this->assertArrayHasKey('order', $payData);
        $this->assertEquals('paid', $payData['order']['payment_status']);
        $this->assertEquals('processing', $payData['order']['status']);
        $this->assertEquals(59.98, $payData['order']['total_amount']);

        $orderId     = $payData['order']['id'];
        $orderNumber = $payData['order']['order_number'];

        // 5. Verify order in DB
        $order = Order::find($orderId);
        $this->assertNotNull($order);
        $this->assertEquals('paid', $order->payment_status);
        // With sync queue, AutoDeliverOrderJob runs immediately and may complete the order
        $this->assertContains($order->status, ['processing', 'completed']);
        $this->assertEquals($this->buyer->id, $order->user_id);
        $this->assertEquals('59.98', $order->total_amount);
        $this->assertEquals('wallet', $order->payment_method);
        $this->assertNotNull($order->paid_at);

        // 6. Verify order items
        $items = OrderItem::where('order_id', $orderId)->get();
        $this->assertCount(1, $items);
        $this->assertEquals($this->offer->id, $items[0]->seller_offer_id);
        $this->assertEquals(2, $items[0]->quantity);
        $this->assertEquals('29.99', $items[0]->unit_price);
        $this->assertEquals('59.98', $items[0]->subtotal);
        $this->assertEquals($this->seller->id, $items[0]->seller_id);

        // 7. Verify billing address
        $address = OrderAddress::where('order_id', $orderId)->where('type', 'billing')->first();
        $this->assertNotNull($address);
        $this->assertEquals('Test Buyer', $address->name);
        $this->assertEquals('buyer@test.com', $address->email);
        $this->assertEquals('US', $address->country);

        // 8. Verify transaction
        $trx = Transaction::where('reference_id', $orderId)
            ->where('reference_type', Order::class)
            ->first();
        $this->assertNotNull($trx);
        $this->assertEquals('completed', $trx->status);
        $this->assertEquals('59.98', $trx->amount);
        $this->assertEquals('wallet', $trx->payment_method);
        $this->assertEquals('debit', $trx->type);
        $this->assertStringStartsWith('trx_', $trx->trx);

        // 9. Verify wallet deducted
        $wallet->refresh();
        $this->assertEquals('40.02', $wallet->balance);

        $walletTrx = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('source', 'order')
            ->where('status', 'completed')
            ->latest('id')
            ->first();
        $this->assertNotNull($walletTrx);
        $this->assertEquals('59.98', $walletTrx->amount);

        // 10. Verify seller earnings created
        $earnings = SellerEarning::where('order_id', $orderId)->get();
        $this->assertCount(1, $earnings);

        $earning = $earnings->first();
        $this->assertEquals($this->seller->id, $earning->seller_id);
        $this->assertEquals('59.98', $earning->gross_amount);
        $expectedCommission = round(59.98 * 10.00 / 100, 2); // 10% of 59.98 = 6.00
        $this->assertEquals($expectedCommission, (float) $earning->commission);
        $expectedNet = bcsub('59.98', $expectedCommission, 2);
        $this->assertEquals($expectedNet, $earning->net_amount);

        // 11. Verify seller balance updated (pending)
        $sellerBalance = SellerBalance::where('seller_id', $this->seller->id)->first();
        $this->assertNotNull($sellerBalance);
        $this->assertEquals($expectedNet, $sellerBalance->pending_balance);
        $this->assertEquals($expectedNet, $sellerBalance->total_earned);
        $this->assertEquals('0.00', $sellerBalance->available_balance);

        // 12. Verify keys marked as sold
        $soldKeys = SellerOfferKey::where('seller_offer_id', $this->offer->id)
            ->where('status', 'sold')
            ->count();
        $this->assertEquals(2, $soldKeys);

        $availableKeys = SellerOfferKey::where('seller_offer_id', $this->offer->id)
            ->where('status', 'available')
            ->count();
        $this->assertEquals(3, $availableKeys);

        // 13. Verify delivery completed (keys assigned)
        $delivery = OrderDelivery::where('order_item_id', $items[0]->id)->first();
        $this->assertNotNull($delivery);
        $this->assertEquals('delivered', $delivery->status);
        $this->assertNotNull($delivery->payload);
        $this->assertEquals('license', $delivery->payload['type']);
        $this->assertCount(2, $delivery->payload['keys']);

        // 14. Verify invoice generated
        $invoice = Invoice::where('order_id', $orderId)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals('59.98', $invoice->grand_total);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);

        $invoiceItems = InvoiceItem::where('invoice_id', $invoice->id)->get();
        $this->assertCount(1, $invoiceItems);
        $this->assertEquals('GTA V Steam Key', $invoiceItems[0]->item_name);

        // 15. Verify order notes
        $notes = OrderNote::where('order_id', $orderId)->get();
        $this->assertTrue($notes->count() >= 1);

        // 16. Verify checkout session completed
        $session->refresh();
        $this->assertEquals('completed', $session->status);
        $this->assertNotNull($session->paid_at);

        // 17. Verify notifications sent
        Notification::assertSentTo($this->buyer, OrderPlacedNotification::class);
        Notification::assertSentTo($this->buyer, OrderPaymentNotification::class);

        // 18. Poll result endpoint
        $resultResponse = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson("/api/v1/checkout/sessions/{$sessionUuid}/result");

        $resultResponse->assertStatus(200);
        $resultData = $resultResponse->json('data');
        $this->assertEquals('completed', $resultData['status']);
        $this->assertEquals($orderId, $resultData['order']['id']);
    }

    /* ================================================================
     |  TEST: Insufficient Stock
     | ================================================================ */

    public function test_checkout_fails_on_insufficient_stock(): void
    {
        $this->creditBuyerWallet(500.00);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/checkout/sessions', [
            'items' => [
                ['seller_offer_id' => $this->offer->id, 'quantity' => 99],
            ],
            'billing' => [
                'name' => 'Test', 'email' => 'test@test.com',
                'address' => '123 St', 'city' => 'NY', 'country' => 'US',
            ],
            'currency' => 'USD',
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Insufficient stock', $response->json('message'));
    }

    /* ================================================================
     |  TEST: Insufficient Wallet Balance
     | ================================================================ */

    public function test_checkout_fails_on_insufficient_wallet(): void
    {
        $this->creditBuyerWallet(10.00); // Not enough for 29.99

        $sessionResponse = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/checkout/sessions', [
            'items' => [
                ['seller_offer_id' => $this->offer->id, 'quantity' => 1],
            ],
            'billing' => [
                'name' => 'Test', 'email' => 'test@test.com',
                'address' => '123 St', 'city' => 'NY', 'country' => 'US',
            ],
            'currency' => 'USD',
        ]);

        $sessionResponse->assertStatus(201);
        $uuid = $sessionResponse->json('data.session_uuid');

        $payResponse = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson("/api/v1/checkout/sessions/{$uuid}/pay", [
            'payment_method' => 'wallet',
        ]);

        $payResponse->assertStatus(422);
        $this->assertStringContainsString('Insufficient wallet balance', $payResponse->json('message'));
    }

    /* ================================================================
     |  TEST: Expired Session Rejected
     | ================================================================ */

    public function test_expired_session_cannot_be_paid(): void
    {
        $this->creditBuyerWallet(100.00);

        $sessionResponse = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/checkout/sessions', [
            'items' => [
                ['seller_offer_id' => $this->offer->id, 'quantity' => 1],
            ],
            'billing' => [
                'name' => 'Test', 'email' => 'test@test.com',
                'address' => '123 St', 'city' => 'NY', 'country' => 'US',
            ],
            'currency' => 'USD',
        ]);

        $uuid = $sessionResponse->json('data.session_uuid');

        // Manually expire the session
        CheckoutSession::where('uuid', $uuid)->update([
            'expires_at' => now()->subMinutes(5),
        ]);

        $payResponse = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson("/api/v1/checkout/sessions/{$uuid}/pay", [
            'payment_method' => 'wallet',
        ]);

        $payResponse->assertStatus(422);
        $this->assertStringContainsString('expired', $payResponse->json('message'));
    }

    /* ================================================================
     |  TEST: Escrow Hold After Auto-Delivery
     | ================================================================ */

    public function test_escrow_hold_on_order_completion(): void
    {
        Notification::fake();
        $this->creditBuyerWallet(100.00);

        // Create and pay
        $sessionResponse = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/checkout/sessions', [
            'items' => [
                ['seller_offer_id' => $this->offer->id, 'quantity' => 1],
            ],
            'billing' => [
                'name' => 'Test', 'email' => 'test@test.com',
                'address' => '123 St', 'city' => 'NY', 'country' => 'US',
            ],
            'currency' => 'USD',
        ]);

        $uuid = $sessionResponse->json('data.session_uuid');

        $payResponse = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson("/api/v1/checkout/sessions/{$uuid}/pay", [
            'payment_method' => 'wallet',
        ]);

        $orderId = $payResponse->json('data.order.id');
        $order   = Order::find($orderId);

        // Order should auto-complete since delivery is done
        // The AutoDeliverOrderJob runs synchronously (queue=sync)
        // and fires OrderCompleted, which triggers StartEscrowHold
        $order->refresh();

        $earning = SellerEarning::where('order_id', $orderId)->first();

        // After checkout fulfillment, earnings start as 'pending'.
        // AutoDeliverOrderJob then runs (sync queue), marks order completed,
        // fires OrderCompleted event, and StartEscrowHold changes to 'held'.
        if ($order->status === 'completed') {
            $earning->refresh();
            $this->assertEquals('held', $earning->status);
            $this->assertNotNull($earning->escrow_expires_at);
            $this->assertTrue($earning->escrow_expires_at->isFuture());
        } else {
            // If delivery didn't auto-complete, earnings remain pending
            $this->assertEquals('pending', $earning->status);
        }
    }

    /* ================================================================
     |  TEST: Session Result Polling
     | ================================================================ */

    public function test_result_endpoint_returns_correct_statuses(): void
    {
        $this->creditBuyerWallet(100.00);

        $sessionResponse = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/checkout/sessions', [
            'items' => [
                ['seller_offer_id' => $this->offer->id, 'quantity' => 1],
            ],
            'billing' => [
                'name' => 'Test', 'email' => 'test@test.com',
                'address' => '123 St', 'city' => 'NY', 'country' => 'US',
            ],
            'currency' => 'USD',
        ]);

        $uuid = $sessionResponse->json('data.session_uuid');

        // Before payment — status is "open"
        $result = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson("/api/v1/checkout/sessions/{$uuid}/result");

        $result->assertStatus(200);
        $this->assertEquals('open', $result->json('data.status'));

        // Pay with wallet (instant)
        $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson("/api/v1/checkout/sessions/{$uuid}/pay", [
            'payment_method' => 'wallet',
        ]);

        // After payment — status is "completed"
        $result = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson("/api/v1/checkout/sessions/{$uuid}/result");

        $result->assertStatus(200);
        $this->assertEquals('completed', $result->json('data.status'));
        $this->assertArrayHasKey('order', $result->json('data'));
    }

    /* ================================================================
     |  TEST: Cannot Access Another User's Session
     | ================================================================ */

    public function test_cannot_access_other_users_session(): void
    {
        $this->creditBuyerWallet(100.00);

        $sessionResponse = $this->actingAs($this->buyer, 'sanctum')
            ->postJson('/api/v1/checkout/sessions', [
                'items' => [
                    ['seller_offer_id' => $this->offer->id, 'quantity' => 1],
                ],
                'billing' => [
                    'name' => 'Test', 'email' => 'test@test.com',
                    'address' => '123 St', 'city' => 'NY', 'country' => 'US',
                ],
                'currency' => 'USD',
            ]);

        $uuid = $sessionResponse->json('data.session_uuid');

        // Another user tries to pay
        $otherUser = User::factory()->create(['username' => 'other', 'is_active' => true, 'is_verified' => true]);

        // Reset app auth state
        app('auth')->forgetGuards();

        $payResponse = $this->actingAs($otherUser, 'sanctum')
            ->postJson("/api/v1/checkout/sessions/{$uuid}/pay", [
                'payment_method' => 'wallet',
            ]);

        $payResponse->assertStatus(404);

        $resultResponse = $this->actingAs($otherUser, 'sanctum')
            ->getJson("/api/v1/checkout/sessions/{$uuid}/result");

        $resultResponse->assertStatus(404);
    }

    /* ================================================================
     |  TEST: Validation Errors
     | ================================================================ */

    public function test_create_session_validation(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/checkout/sessions', []);

        $response->assertStatus(422);
    }

    public function test_pay_validation(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/checkout/sessions/fake-uuid/pay', []);

        $response->assertStatus(422);
    }

    /* ================================================================
     |  TEST: Multiple Items Checkout
     | ================================================================ */

    public function test_multiple_items_checkout(): void
    {
        Notification::fake();

        // Create a second product + offer
        $product2 = Product::create([
            'title'  => 'Cyberpunk 2077 Key',
            'slug'   => 'cyberpunk-2077-key',
            'sku'    => 'CP77-001',
            'short_description' => 'Cyberpunk 2077',
            'description'       => 'Full game.',
            'image'             => 'products/cp77.jpg',
            'delivery_type'     => 'auto',
            'status'            => 'active',
        ]);

        $offer2 = SellerOffer::create([
            'seller_id'    => $this->seller->id,
            'product_id'   => $product2->id,
            'retail_price' => 19.99,
            'sale_mode'    => 'retail',
            'status'       => 'active',
            'is_verified'  => true,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            SellerOfferKey::create([
                'seller_offer_id' => $offer2->id,
                'type'            => 'text',
                'value'           => "CP77-KEY-{$i}",
                'status'          => 'available',
            ]);
        }

        $this->creditBuyerWallet(200.00);

        $sessionResponse = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/checkout/sessions', [
            'items' => [
                ['seller_offer_id' => $this->offer->id, 'quantity' => 1],
                ['seller_offer_id' => $offer2->id, 'quantity' => 2],
            ],
            'billing' => [
                'name' => 'Buyer', 'email' => 'buyer@test.com',
                'address' => '123 St', 'city' => 'LA', 'country' => 'US',
            ],
            'currency' => 'USD',
        ]);

        $sessionResponse->assertStatus(201);
        $total = $sessionResponse->json('data.total_amount');
        $this->assertEquals(69.97, $total); // 29.99 + (19.99 * 2)

        $uuid = $sessionResponse->json('data.session_uuid');

        $payResponse = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson("/api/v1/checkout/sessions/{$uuid}/pay", [
            'payment_method' => 'wallet',
        ]);

        $payResponse->assertStatus(200);
        $this->assertEquals('completed', $payResponse->json('data.status'));

        $orderId = $payResponse->json('data.order.id');

        // 2 order items
        $items = OrderItem::where('order_id', $orderId)->get();
        $this->assertCount(2, $items);

        // 2 seller earnings
        $earnings = SellerEarning::where('order_id', $orderId)->get();
        $this->assertCount(2, $earnings);

        // 3 keys sold total (1 from offer1 + 2 from offer2)
        $delivery1 = OrderDelivery::where('order_item_id', $items->where('seller_offer_id', $this->offer->id)->first()->id)->first();
        $delivery2 = OrderDelivery::where('order_item_id', $items->where('seller_offer_id', $offer2->id)->first()->id)->first();

        $this->assertEquals('delivered', $delivery1->status);
        $this->assertEquals('delivered', $delivery2->status);
        $this->assertCount(1, $delivery1->payload['keys']);
        $this->assertCount(2, $delivery2->payload['keys']);
    }
}
