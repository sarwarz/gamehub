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

            'items' => 'required|array|min:1',
            'items.*.seller_offer_id' => 'required|exists:seller_offers,id',
            'items.*.quantity' => 'required|integer|min:1',

            'billing.name' => 'required|string',
            'billing.email' => 'required|email',
            'billing.address' => 'required|string',
            'billing.city' => 'required|string',
            'billing.country' => 'required|string',

            'payment_method' => 'required|string',
        ];
    }
}
