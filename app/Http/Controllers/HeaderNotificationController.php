<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HeaderNotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::user();

        $notifications = $user->notifications()
            ->latest()
            ->limit(10)
            ->get();

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications->map(fn ($n) => [
                'id'        => $n->id,
                'title'     => $n->data['title'] ?? 'Notification',
                'message'   => $n->data['message'] ?? '',
                'type'      => $n->data['type'] ?? 'info',
                'icon'      => $n->data['icon'] ?? 'tabler-bell',
                'url'       => $n->data['url'] ?? null,
                'read'      => $n->read_at !== null,
                'time_ago'  => $n->created_at?->diffForHumans(),
            ]),
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(string $id): JsonResponse
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['status' => true]);
    }

    public function markAllAsRead(): JsonResponse
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['status' => true]);
    }
}
