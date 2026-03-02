<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\SupportTicket;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group User Dashboard
 *
 * APIs for the customer dashboard providing aggregated stats,
 * recent activity, and account summary.
 */
class UserDashboardController extends Controller
{
    /**
     * Dashboard overview
     *
     * Get comprehensive customer dashboard stats including order summary,
     * wallet balance, recent orders, wishlist count, and support ticket summary.
     *
     * @authenticated
     *
     * @response 200 {"status":true,"message":"Dashboard loaded","data":{"stats":{...},"recent_orders":[],"wallet":null}}
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $userId = $user->id;

            $orders = Order::where('user_id', $userId);

            $stats = [
                'total_orders'     => $orders->clone()->count(),
                'pending_orders'   => $orders->clone()->where('status', 'pending')->count(),
                'completed_orders' => $orders->clone()->where('status', 'completed')->count(),
                'cancelled_orders' => $orders->clone()->where('status', 'cancelled')->count(),
                'total_spent'      => $orders->clone()->where('payment_status', 'paid')->sum('total_amount'),
                'wishlist_count'   => Wishlist::where('user_id', $userId)->count(),
                'open_tickets'     => SupportTicket::where('user_id', $userId)->whereIn('status', ['open', 'waiting'])->count(),
                'reviews_count'    => ProductReview::where('user_id', $userId)->count(),
            ];

            $recentOrders = Order::where('user_id', $userId)
                ->with(['items' => fn ($q) => $q->with('product:id,title,slug,image')])
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($o) => [
                    'id'             => $o->id,
                    'order_number'   => $o->order_number,
                    'total_amount'   => $o->total_amount,
                    'status'         => $o->status,
                    'payment_status' => $o->payment_status,
                    'items_count'    => $o->items->count(),
                    'items_preview'  => $o->items->take(3)->map(fn ($i) => [
                        'product' => $i->product?->only(['id', 'title', 'slug', 'image']),
                    ]),
                    'created_at'     => $o->created_at->toISOString(),
                ]);

            $wallet = $user->wallet;

            $recentNotifications = $user->notifications()
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($n) => [
                    'id'         => $n->id,
                    'type'       => class_basename($n->type),
                    'data'       => $n->data,
                    'read_at'    => $n->read_at?->toISOString(),
                    'created_at' => $n->created_at->toISOString(),
                ]);

            return $this->success([
                'stats'                => $stats,
                'recent_orders'        => $recentOrders,
                'wallet'               => $wallet ? [
                    'balance'          => $wallet->balance,
                    'is_active'        => $wallet->is_active ?? true,
                ] : null,
                'recent_notifications' => $recentNotifications,
                'unread_notifications' => $user->unreadNotifications()->count(),
            ], 'Dashboard loaded');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to load dashboard.', 500);
        }
    }
}
