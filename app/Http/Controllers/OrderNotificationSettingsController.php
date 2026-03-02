<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OrderNotificationSettingsController extends Controller
{
    private array $keys = [
        'customer_on_placed',
        'seller_on_placed',
        'admin_on_placed',
        'customer_on_paid',
        'admin_on_paid',
        'customer_on_status_change',
        'customer_on_completed',
        'seller_on_completed',
        'customer_on_cancelled',
        'seller_on_cancelled',
        'admin_on_cancelled',
        'customer_on_refunded',
        'seller_on_refunded',
        'customer_on_delivery',
        'admin_on_delivery_failed',
    ];

    public function index()
    {
        $settings = Setting::group('order_notifications');
        return view('content.orders.notification-settings', compact('settings'));
    }

    public function update(Request $request)
    {
        foreach ($this->keys as $key) {
            Setting::set('order_notifications', $key, $request->boolean($key));
        }

        Cache::forget('settings.group.order_notifications');

        return response()->json(['message' => 'Order notification settings saved successfully.']);
    }
}
