<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\SellerOfferKey;
use Illuminate\Support\Facades\DB;
use Exception;

class LicenseDeliveryService
{
    /**
     * Auto deliver license keys for an order item
     */
    public function deliver(OrderItem $item): array
    {
        return DB::transaction(function () use ($item) {

            // 1️⃣ Fetch available keys (LOCKED)
            $keys = SellerOfferKey::where('seller_offer_id', $item->seller_offer_id)
                ->where('status', 'available')
                ->lockForUpdate()
                ->limit($item->quantity)
                ->get();

            if ($keys->count() < $item->quantity) {
                throw new Exception('Insufficient license stock');
            }

            // 2️⃣ Reserve & mark as sold
            foreach ($keys as $key) {
                $key->update([
                    'status' => 'sold',
                ]);
            }

            // 3️⃣ Return delivered payload
            return [
                'type' => 'license',
                'keys' => $keys->map(fn ($k) => $k->value)->values(),
            ];
        });
    }
}
