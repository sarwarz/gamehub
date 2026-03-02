<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Notifications
 *
 * APIs for managing user notifications.
 * All endpoints require authentication.
 */
class NotificationController extends Controller
{
    /**
     * List notifications
     *
     * Get the authenticated user's notifications with pagination.
     *
     * @authenticated
     *
     * @queryParam unread_only boolean Only return unread notifications. Example: true
     * @queryParam per_page integer Results per page (default 15). Example: 20
     *
     * @response 200 {"status":true,"message":"Notifications fetched","data":{"current_page":1,"data":[],"total":0}}
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            $query = $request->user()->notifications();

            if ($request->boolean('unread_only')) {
                $query = $request->user()->unreadNotifications();
            }

            $notifications = $query->paginate($request->input('per_page', 15));

            $notifications->getCollection()->transform(fn ($n) => [
                'id'         => $n->id,
                'type'       => class_basename($n->type),
                'data'       => $n->data,
                'read_at'    => $n->read_at?->toISOString(),
                'created_at' => $n->created_at->toISOString(),
            ]);

            return $this->success($notifications, 'Notifications fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch notifications.', 500);
        }
    }

    /**
     * Unread count
     *
     * Get the number of unread notifications.
     *
     * @authenticated
     *
     * @response 200 {"status":true,"message":"Count fetched","data":{"unread_count":5}}
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            return $this->success([
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ], 'Count fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch unread count.', 500);
        }
    }

    /**
     * Mark as read
     *
     * Mark a specific notification as read.
     *
     * @authenticated
     *
     * @urlParam id string required Notification UUID. Example: 550e8400-e29b-41d4-a716-446655440000
     *
     * @response 200 {"status":true,"message":"Notification marked as read"}
     * @response 404 {"status":false,"message":"Notification not found"}
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        try {
            $notification = $request->user()->notifications()->find($id);

            if (!$notification) {
                return $this->error('Notification not found', 404);
            }

            $notification->markAsRead();

            return $this->success(null, 'Notification marked as read');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to mark notification as read.', 500);
        }
    }

    /**
     * Mark all as read
     *
     * Mark all unread notifications as read.
     *
     * @authenticated
     *
     * @response 200 {"status":true,"message":"All notifications marked as read","data":{"marked":3}}
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $count = $request->user()->unreadNotifications()->count();
            $request->user()->unreadNotifications()->update(['read_at' => now()]);

            return $this->success(['marked' => $count], 'All notifications marked as read');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to mark notifications as read.', 500);
        }
    }

    /**
     * Delete notification
     *
     * Delete a specific notification.
     *
     * @authenticated
     *
     * @urlParam id string required Notification UUID.
     *
     * @response 200 {"status":true,"message":"Notification deleted"}
     * @response 404 {"status":false,"message":"Notification not found"}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $notification = $request->user()->notifications()->find($id);

            if (!$notification) {
                return $this->error('Notification not found', 404);
            }

            $notification->delete();

            return $this->success(null, 'Notification deleted');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to delete notification.', 500);
        }
    }
}
