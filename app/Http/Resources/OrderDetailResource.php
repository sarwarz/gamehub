<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order' => [
                'id'             => $this->id,
                'order_number'   => $this->order_number,
                'status'         => $this->status,
                'payment_status' => $this->payment_status,
                'payment_method' => $this->payment_method,
                'currency'       => $this->currency,
                'subtotal'       => (float) $this->subtotal,
                'tax_amount'     => (float) $this->tax_amount,
                'discount'       => (float) $this->discount_amount,
                'total_amount'   => (float) $this->total_amount,
                'created_at'     => $this->created_at?->toISOString(),
            ],

            'items' => $this->items->map(function ($item) {
                return [
                    'id'        => $item->id,
                    'quantity'  => $item->quantity,
                    'price'     => (float) $item->unit_price,
                    'subtotal'  => (float) $item->subtotal,

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
                            'id'            => $delivery->id,
                            'method'        => $delivery->delivery_method,
                            'status'        => $delivery->status,
                            'delivered_at'  => optional($delivery->delivered_at)?->toISOString(),

                            // Only expose payload if delivered
                            'payload' => $delivery->status === 'delivered'
                                ? $delivery->payload
                                : null,
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
                    'name',
                    'email',
                    'phone',
                    'address',
                    'city',
                    'state',
                    'postal_code',
                    'country',
                ]),
            ],

            'meta' => [
                'client'   => $this->meta['client'] ?? null,
                'checkout' => $this->meta['checkout'] ?? null,
            ],
        ];
    }
}
