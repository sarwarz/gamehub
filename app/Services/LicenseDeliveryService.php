<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\SellerOfferKey;
use Illuminate\Support\Facades\DB;
use Exception;

class LicenseDeliveryService
{
    /**
     * Auto deliver license keys for an order item.
     * Prefers pre-reserved keys (from checkout session), falls back to fresh stock.
     */
    public function deliver(OrderItem $item): array
    {
        return DB::transaction(function () use ($item) {

            $delivery = $item->deliveries()->first();

            if ($delivery && $delivery->status === 'delivered' && !empty($delivery->payload)) {
                return $delivery->payload;
            }

            $keys = SellerOfferKey::where('seller_offer_id', $item->seller_offer_id)
                ->where(function ($q) {
                    $q->where('status', 'available')
                      ->orWhere('status', 'reserved');
                })
                ->lockForUpdate()
                ->limit($item->quantity)
                ->get();

            if ($keys->count() < $item->quantity) {
                throw new Exception('Insufficient license stock');
            }

            foreach ($keys as $key) {
                $key->update([
                    'status'              => 'sold',
                    'reserved_at'         => null,
                    'reserved_until'      => null,
                    'reserved_session_id' => null,
                ]);
            }

            return [
                'type' => 'license',
                'keys' => $keys->map(fn ($k) => $k->value)->values(),
            ];
        });
    }
}
