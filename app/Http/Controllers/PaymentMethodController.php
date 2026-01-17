<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Services\CountryService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
    public function index()
    {

        $methods = PaymentMethod::orderBy('sort_order')->get();

        // Default selected gateway
        $activeMethod = $methods->first();
        $currencies = Currency::where('is_active', true)->get();

        return view('content.payment-methods.index', compact('methods', 'activeMethod', 'currencies'));
    }

    public function edit(string $code)
    {
        $methods = PaymentMethod::orderBy('sort_order')->get();
        $activeMethod = PaymentMethod::where('code', $code)->firstOrFail();
        $currencies = Currency::where('is_active', true)->get();

        return view('content.payment-methods.index', compact('methods', 'activeMethod', 'currencies'));
    }

    public function update(Request $request, string $code)
    {
        $method = PaymentMethod::where('code', $code)->firstOrFail();

        $data = $request->validate([
            'is_enabled' => 'nullable|boolean',
            'mode'       => 'required|in:sandbox,live',
            'country'    => 'nullable|string',
            'currency'   => 'nullable|string',
            'rate'       => 'required|numeric|min:0',
            'config'     => 'nullable|array',
        ]);

        $data['is_enabled'] = $request->has('is_enabled');

        $method->update($data);

        return redirect()
            ->route('payment-methods.edit', $code)
            ->with('success', 'Payment method updated successfully');
    }
}
