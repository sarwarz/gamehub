<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;
use Yajra\DataTables\Facades\DataTables;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() && $request->wantsJson()) {
            $query = DatabaseNotification::with('notifiable')->latest();

            if ($request->filled('type')) {
                $query->where('type', 'like', "%{$request->type}%");
            }

            if ($request->filled('read_status')) {
                if ($request->read_status === 'read') {
                    $query->whereNotNull('read_at');
                } else {
                    $query->whereNull('read_at');
                }
            }

            return DataTables::eloquent($query)
                ->addColumn('checkbox', function ($n) {
                    return '<input type="checkbox" class="bulk-checkbox" value="' . $n->id . '">';
                })
                ->addColumn('recipient', function ($n) {
                    $user = $n->notifiable;
                    if (!$user) return '<span class="text-muted">Deleted User</span>';
                    return '<div class="d-flex align-items-center">'
                        . '<div class="avatar avatar-sm me-2 bg-label-primary"><span class="avatar-initial rounded-circle">'
                        . strtoupper(substr($user->name, 0, 1))
                        . '</span></div>'
                        . '<div><span class="fw-medium">' . e($user->name) . '</span>'
                        . '<br><small class="text-muted">' . e($user->email) . '</small></div></div>';
                })
                ->addColumn('type_label', function ($n) {
                    $label = class_basename($n->type);
                    $colors = [
                        'OrderPlacedNotification'   => 'bg-label-success',
                        'OrderPaymentNotification'  => 'bg-label-warning',
                        'OrderStatusNotification'   => 'bg-label-info',
                        'AdminBroadcastNotification' => 'bg-label-primary',
                        'TicketCreatedNotification' => 'bg-label-danger',
                        'TicketReplyNotification'   => 'bg-label-secondary',
                    ];
                    $color = $colors[$label] ?? 'bg-label-secondary';
                    return '<span class="badge ' . $color . '">' . $label . '</span>';
                })
                ->addColumn('status_badge', function ($n) {
                    return $n->read_at
                        ? '<span class="badge bg-label-secondary"><i class="ti tabler-eye-check me-1"></i>Read</span>'
                        : '<span class="badge bg-label-primary"><i class="ti tabler-eye me-1"></i>Unread</span>';
                })
                ->addColumn('preview', function ($n) {
                    $data  = $n->data;
                    $title = e($data['title'] ?? '');
                    $msg   = e(\Illuminate\Support\Str::limit($data['message'] ?? '', 50));
                    return '<strong>' . $title . '</strong><br><small class="text-muted">' . $msg . '</small>';
                })
                ->addColumn('actions', function ($n) {
                    $payload = e(json_encode([
                        'id'        => $n->id,
                        'type'      => class_basename($n->type),
                        'title'     => $n->data['title'] ?? '',
                        'message'   => $n->data['message'] ?? '',
                        'url'       => $n->data['url'] ?? null,
                        'extra'     => collect($n->data)->except(['title', 'message', 'url', 'type'])->toArray(),
                        'recipient' => $n->notifiable ? $n->notifiable->name . ' (' . $n->notifiable->email . ')' : 'Deleted User',
                        'status'    => $n->read_at ? 'Read' : 'Unread',
                        'read_at'   => $n->read_at?->format('M d, Y h:i A'),
                        'created_at'=> $n->created_at->format('M d, Y h:i A'),
                    ]));

                    return '<div class="d-flex align-items-center justify-content-center gap-1">'
                        . '<button type="button" class="btn btn-icon btn-sm btn-text-primary view-btn" data-notification=\'' . $payload . '\' title="View">'
                        . '<i class="icon-base ti tabler-eye icon-md"></i></button>'
                        . '<button type="button" class="btn btn-icon btn-sm btn-text-danger delete-btn" data-id="' . $n->id . '" title="Delete">'
                        . '<i class="icon-base ti tabler-trash icon-md"></i></button>'
                        . '</div>';
                })
                ->rawColumns(['checkbox', 'recipient', 'type_label', 'status_badge', 'preview', 'actions'])
                ->make(true);
        }

        $stats = [
            'total'  => DatabaseNotification::count(),
            'unread' => DatabaseNotification::whereNull('read_at')->count(),
            'read'   => DatabaseNotification::whereNotNull('read_at')->count(),
        ];

        $roles = Role::orderBy('name')->pluck('name', 'id');

        return view('content.notifications.index', compact('stats', 'roles'));
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $term = $request->get('q', '');

        $users = User::where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('id', $term);
            })
            ->select('id', 'name', 'email')
            ->limit(20)
            ->get()
            ->map(fn ($u) => [
                'id'   => $u->id,
                'text' => $u->name . ' (' . $u->email . ')',
            ]);

        return response()->json(['results' => $users]);
    }

    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids'  => 'required|array|min:1',
            'user_ids.*'=> 'exists:users,id',
            'title'     => 'required|string|max:255',
            'message'   => 'required|string|max:2000',
            'type'      => 'nullable|string|max:50',
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();

        foreach ($users as $user) {
            $user->notify(new \App\Notifications\AdminBroadcastNotification(
                $request->title,
                $request->message,
                $request->type ?? 'info'
            ));
        }

        return response()->json([
            'message' => 'Notification sent to ' . $users->count() . ' user(s).',
        ]);
    }

    public function sendToAll(Request $request): JsonResponse
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'role'    => 'nullable|string',
        ]);

        $query = User::query();

        if ($request->role && $request->role !== 'all') {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->role));
        }

        $count = 0;
        $query->chunk(100, function ($users) use ($request, &$count) {
            foreach ($users as $user) {
                $user->notify(new \App\Notifications\AdminBroadcastNotification(
                    $request->title,
                    $request->message,
                    'announcement'
                ));
                $count++;
            }
        });

        return response()->json(['message' => "Notification sent to {$count} user(s)."]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = DatabaseNotification::find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted.',
            'stats'   => $this->getStats(),
        ]);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|string',
        ]);

        $deleted = DatabaseNotification::whereIn('id', $data['ids'])->delete();

        return response()->json([
            'message' => $deleted . ' notification(s) deleted.',
            'stats'   => $this->getStats(),
        ]);
    }

    private function getStats(): array
    {
        return [
            'total'  => DatabaseNotification::count(),
            'unread' => DatabaseNotification::whereNull('read_at')->count(),
            'read'   => DatabaseNotification::whereNotNull('read_at')->count(),
        ];
    }
}
