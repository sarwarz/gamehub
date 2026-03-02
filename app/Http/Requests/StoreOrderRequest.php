<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency' => 'required|string|max:10',

            'items'                   => 'required|array|min:1|max:50',
            'items.*.seller_offer_id' => 'required|integer|distinct|exists:seller_offers,id',
            'items.*.quantity'        => 'required|integer|min:1|max:100',

            'billing.name'     => 'required|string|max:255',
            'billing.email'    => 'required|email|max:255',
            'billing.phone'    => 'nullable|string|max:30',
            'billing.address'  => 'required|string|max:500',
            'billing.city'     => 'required|string|max:100',
            'billing.state'    => 'nullable|string|max:100',
            'billing.country'  => 'required|string|max:10',
            'billing.postcode' => 'nullable|string|max:20',

            'payment_method' => 'required|string|in:wallet,stripe,paypal,cryptomus,tazapay,1d3,cod',
            'use_wallet'     => 'sometimes|boolean',
            'coupon_code'    => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'items.*.seller_offer_id.distinct' => 'Duplicate seller offers are not allowed. Increase quantity instead.',
            'payment_method.in'                => 'Invalid payment method selected.',
        ];
    }
}
