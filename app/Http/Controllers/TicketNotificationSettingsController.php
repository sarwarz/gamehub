<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TicketNotificationSettingsController extends Controller
{
    private array $keys = [
        'on_ticket_created',
        'admin_on_new_ticket',
        'on_staff_reply',
        'on_customer_reply',
        'on_status_change',
        'on_ticket_closed',
        'on_assigned',
        'on_escalated',
    ];

    public function index()
    {
        $settings = Setting::group('ticket_notifications');

        return view('content.support-tickets.notification-settings', compact('settings'));
    }

    public function update(Request $request)
    {
        foreach ($this->keys as $key) {
            Setting::set('ticket_notifications', $key, $request->boolean($key));
        }

        Cache::forget('settings.group.ticket_notifications');

        return response()->json(['message' => 'Notification settings saved successfully.']);
    }
}
