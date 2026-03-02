<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SellerOfferKey;
use App\Models\CheckoutSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KeyReservationService
{
    /**
     * Soft-lock keys for each item in the checkout session.
     * Returns the array of reserved key IDs.
     */
    public function reserve(CheckoutSession $session, array $items): array
    {
        return DB::transaction(function () use ($session, $items) {

            $allReservedIds = [];

            foreach ($items as $item) {
                $keys = SellerOfferKey::where('seller_offer_id', $item['seller_offer_id'])
                    ->where('status', 'available')
                    ->lockForUpdate()
                    ->limit($item['quantity'])
                    ->get();

                if ($keys->count() < $item['quantity']) {
                    $this->rollbackReserved($allReservedIds);
                    throw new \RuntimeException(
                        "Insufficient stock for \"{$item['product_title']}\" during key reservation."
                    );
                }

                $ids = $keys->pluck('id')->toArray();

                SellerOfferKey::whereIn('id', $ids)->update([
                    'status'              => 'reserved',
                    'reserved_at'         => now(),
                    'reserved_until'      => $session->expires_at,
                    'reserved_session_id' => $session->id,
                ]);

                $allReservedIds = array_merge($allReservedIds, $ids);
            }

            return $allReservedIds;
        });
    }

    /**
     * Release all keys reserved for a session.
     */
    public function release(CheckoutSession $session): void
    {
        SellerOfferKey::where('reserved_session_id', $session->id)
            ->where('status', 'reserved')
            ->update([
                'status'              => 'available',
                'reserved_at'         => null,
                'reserved_until'      => null,
                'reserved_session_id' => null,
            ]);
    }

    /**
     * Convert reserved keys to sold and build delivery payloads per order item.
     * Called inside fulfillSession() after the order and items are created.
     */
    public function assign(CheckoutSession $session, Order $order): void
    {
        $reservedIds = $session->reserved_key_ids ?? [];

        if (empty($reservedIds)) {
            return;
        }

        $reservedKeys = SellerOfferKey::whereIn('id', $reservedIds)
            ->where('status', 'reserved')
            ->where('reserved_session_id', $session->id)
            ->get()
            ->groupBy('seller_offer_id');

        $allAssigned = true;

        foreach ($order->items as $orderItem) {
            $keysForOffer = $reservedKeys->get($orderItem->seller_offer_id);

            if (!$keysForOffer || $keysForOffer->isEmpty()) {
                $allAssigned = false;
                continue;
            }

            $keysForOffer->each(function ($key) {
                $key->update([
                    'status'              => 'sold',
                    'reserved_at'         => null,
                    'reserved_until'      => null,
                    'reserved_session_id' => null,
                ]);
            });

            $delivery = $orderItem->deliveries()->first();
            if ($delivery) {
                $delivery->update([
                    'status'       => 'delivered',
                    'payload'      => [
                        'type' => 'license',
                        'keys' => $keysForOffer->pluck('value')->values()->toArray(),
                    ],
                    'delivered_at' => now(),
                ]);
            }

            $orderItem->update(['delivery_status' => 'delivered']);
        }

        if ($allAssigned && $order->status !== 'completed') {
            $hasPending = $order->items()->where('delivery_status', '!=', 'delivered')->exists();

            if (!$hasPending) {
                $order->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ]);
            }
        }
    }

    /**
     * Cleanup: release all keys whose reservation has expired.
     */
    public function cleanupExpired(): int
    {
        return SellerOfferKey::where('status', 'reserved')
            ->where('reserved_until', '<', now())
            ->update([
                'status'              => 'available',
                'reserved_at'         => null,
                'reserved_until'      => null,
                'reserved_session_id' => null,
            ]);
    }

    protected function rollbackReserved(array $ids): void
    {
        if (!empty($ids)) {
            SellerOfferKey::whereIn('id', $ids)->update([
                'status'              => 'available',
                'reserved_at'         => null,
                'reserved_until'      => null,
                'reserved_session_id' => null,
            ]);
        }
    }
}
