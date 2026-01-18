<?php

namespace App\Services;

use App\Models\{
    Order,
    OrderItem,
    OrderItemKey,
    SellerOffer,
    SellerEarning,
    SellerBalance,
    Transaction
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Create a new order
     */
    public function create($user, array $items, string $currency = 'USD'): Order
    {
        return DB::transaction(function () use ($user, $items, $currency) {

            /**
             * 1️⃣ Create base order
             */
            $order = Order::create([
                'user_id'        => $user->id,
                'currency'       => $currency,
                'payment_status' => 'unpaid',
                'status'         => 'pending',
                'subtotal'       => 0,
                'total_amount'   => 0,
            ]);

            $subtotal = 0;

            /**
             * 2️⃣ Process each item
             */
            foreach ($items as $item) {

                $offer = SellerOffer::with('keys')
                    ->where('id', $item['seller_offer_id'])
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->first();

                if (!$offer) {
                    throw ValidationException::withMessages([
                        'offer' => 'Invalid or inactive seller offer.'
                    ]);
                }

                $quantity = (int) $item['quantity'];

                /**
                 * 3️⃣ Stock check
                 */
                $keys = $offer->keys()
                    ->where('status', 'available')
                    ->limit($quantity)
                    ->get();

                if ($keys->count() < $quantity) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for offer #{$offer->id}"
                    ]);
                }

                /**
                 * 4️⃣ Price calculation
                 */
                $unitPrice = $offer->retail_price;
                $lineTotal = $unitPrice * $quantity;

                /**
                 * 5️⃣ Create order item
                 */
                $orderItem = OrderItem::create([
                    'order_id'        => $order->id,
                    'seller_id'       => $offer->seller_id,
                    'product_id'      => $offer->product_id,
                    'seller_offer_id' => $offer->id,
                    'quantity'        => $quantity,
                    'unit_price'      => $unitPrice,
                    'subtotal'        => $lineTotal,
                    'status'          => 'pending',
                ]);

                /**
                 * 6️⃣ Assign keys (CORRECT ENUM)
                 */
                foreach ($keys as $key) {

                    // Seller stock
                    $key->update([
                        'status' => 'assigned'
                    ]);

                    // Order delivery record
                    OrderItemKey::create([
                        'order_item_id'       => $orderItem->id,
                        'seller_offer_key_id' => $key->id,
                        'key_type'            => $key->type,
                        'key_value'           => $key->value,
                        'status'              => 'assigned',
                    ]);
                }

                /**
                 * 7️⃣ Seller earnings
                 */
                $commission = round($lineTotal * 0.10, 2);
                $sellerNet  = $lineTotal - $commission;

                SellerEarning::create([
                    'seller_id'    => $offer->seller_id,
                    'order_id'     => $order->id,
                    'gross_amount' => $lineTotal,
                    'commission'   => $commission,
                    'net_amount'   => $sellerNet,
                    'status'       => 'pending',
                ]);

                /**
                 * 8️⃣ Update seller balance
                 */
                $balance = SellerBalance::firstOrCreate([
                    'seller_id' => $offer->seller_id
                ]);

                $balance->increment('pending_balance', $sellerNet);
                $balance->increment('total_earned', $sellerNet);

                $subtotal += $lineTotal;
            }

            /**
             * 9️⃣ Update order totals
             */
            $order->update([
                'subtotal'     => $subtotal,
                'total_amount' => $subtotal,
            ]);

            /**
             * 🔟 Ledger transaction
             */
            Transaction::create([
                'user_id'        => $user->id,
                'trx'            => (string) Str::uuid(),
                'amount'         => $subtotal,
                'fee'            => 0,
                'net_amount'     => $subtotal,
                'currency'       => $currency,
                'type'           => 'debit',
                'category'       => 'order',
                'status'         => 'pending',
                'payment_method'=> 'checkout',
                'reference_type' => Order::class,
                'reference_id'   => $order->id,
                'description'    => 'Order created',
            ]);

            return $order;
        });
    }
}
