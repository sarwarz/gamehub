<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\User;
use App\Models\SupportTicket;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user->load('roles');
        $isFullAccess = $user->isSuperAdmin() || $user->hasRole('admin');

        $data = ['user' => $user, 'isFullAccess' => $isFullAccess];

        if (!$isFullAccess) {
            $data['basicStats'] = $this->getBasicStats($user);
        }

        return view('dashboard.index', $data);
    }

    private function getBasicStats($user): array
    {
        $stats = [];

        if ($user->hasPermission('orders')) {
            $stats['pending_orders'] = Order::where('status', 'pending')->count();
            $stats['total_orders'] = Order::count();
        }

        if ($user->hasPermission('support-tickets')) {
            $stats['open_tickets'] = SupportTicket::active()->count();
            $stats['escalated_tickets'] = SupportTicket::escalated()->count();
        }

        if ($user->hasPermission('users')) {
            $stats['total_users'] = User::count();
            $stats['new_users_today'] = User::whereDate('created_at', today())->count();
        }

        if ($user->hasPermission('contact-messages')) {
            $stats['unread_messages'] = \App\Models\ContactMessage::unread()->count();
        }

        return $stats;
    }
}
