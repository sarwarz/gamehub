<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order' => [
                'id'              => $this->id,
                'order_number'    => $this->order_number,
                'status'          => $this->status,
                'payment_status'  => $this->payment_status,
                'payment_method'  => $this->payment_method,
                'currency'        => $this->currency,
                'subtotal'        => (float) $this->subtotal,
                'tax_amount'      => (float) $this->tax_amount,
                'discount_amount' => (float) $this->discount_amount,
                'total_amount'    => (float) $this->total_amount,
                'base_currency'        => $this->base_currency,
                'base_subtotal'        => (float) ($this->base_subtotal ?? $this->subtotal),
                'base_tax_amount'      => (float) ($this->base_tax_amount ?? $this->tax_amount),
                'base_discount_amount' => (float) ($this->base_discount_amount ?? $this->discount_amount),
                'base_total_amount'    => (float) ($this->base_total_amount ?? $this->total_amount),
                'exchange_rate'        => (float) ($this->exchange_rate ?? 1),
                'created_at'      => $this->created_at?->toISOString(),
                'paid_at'         => $this->paid_at?->toISOString(),
                'completed_at'    => $this->completed_at?->toISOString(),
                'cancelled_at'    => $this->cancelled_at?->toISOString(),
            ],

            'items' => $this->items->map(function ($item) {
                return [
                    'id'              => $item->id,
                    'quantity'        => $item->quantity,
                    'price'           => (float) $item->unit_price,
                    'subtotal'        => (float) $item->subtotal,
                    'delivery_status' => $item->delivery_status,
                    'status'          => $item->status,

                    'product' => [
                        'id'    => $item->product?->id,
                        'title' => $item->product?->title,
                        'slug'  => $item->product?->slug,
                        'image' => $item->product?->cover_image,
                    ],

                    'offer' => [
                        'id'    => $item->offer?->id,
                        'price' => (float) ($item->offer?->retail_price ?? 0),
                    ],

                    'delivery' => $item->deliveries->map(function ($delivery) {
                        return [
                            'id'           => $delivery->id,
                            'method'       => $delivery->delivery_method,
                            'status'       => $delivery->status,
                            'delivered_at' => optional($delivery->delivered_at)?->toISOString(),
                            'payload'      => $delivery->status === 'delivered' ? $delivery->payload : null,
                        ];
                    }),
                ];
            }),

            'transactions' => $this->transactions->map(function ($txn) {
                return [
                    'trx'        => $txn->trx,
                    'type'       => $txn->type,
                    'category'   => $txn->category,
                    'amount'     => (float) $txn->amount,
                    'status'     => $txn->status,
                    'method'     => $txn->payment_method,
                    'created_at' => $txn->created_at?->toISOString(),
                ];
            }),

            'addresses' => [
                'billing' => optional(
                    $this->addresses->firstWhere('type', 'billing')
                )?->only([
                    'name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country',
                ]),
            ],

            'wallet'      => $this->meta['wallet'] ?? null,
            'tax_details' => $this->meta['tax_details'] ?? [],
            'coupon'      => $this->meta['coupon'] ?? null,

            'meta' => [
                'client'   => $this->meta['client'] ?? null,
                'checkout' => $this->meta['checkout'] ?? null,
            ],
        ];
    }
}
