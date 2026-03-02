<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\SupportTicket;
use App\Models\TicketDepartment;
use App\Services\TicketNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Support Tickets
 *
 * Manage support tickets as an authenticated customer.
 * Customers can create tickets, view their ticket history,
 * reply to conversations, escalate seller tickets to admin, and close tickets.
 *
 * @authenticated
 */
class SupportTicketController extends Controller
{
    /**
     * List my tickets
     *
     * Returns a paginated list of the authenticated user's support tickets.
     *
     * @queryParam status string Filter by status. Example: open
     * @queryParam department string Filter by department. Example: order
     * @queryParam per_page int Items per page (default 15). Example: 15
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Tickets fetched successfully",
     *   "data": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": 1,
     *         "ticket_number": "TKT-000001",
     *         "subject": "Missing game key",
     *         "department": "order",
     *         "priority": "high",
     *         "status": "awaiting_seller",
     *         "is_escalated": false,
     *         "created_at": "2026-02-28T12:00:00.000000Z",
     *         "last_reply_at": "2026-02-28T14:30:00.000000Z",
     *         "seller": {
     *           "id": 1,
     *           "store_name": "GameKeys Pro"
     *         },
     *         "messages_count": 3
     *       }
     *     ],
     *     "total": 5
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status'     => 'sometimes|in:' . implode(',', SupportTicket::STATUSES),
            'department' => 'sometimes|in:' . implode(',', SupportTicket::departmentSlugs()),
            'per_page'   => 'sometimes|integer|min:1|max:100',
        ]);

        try {
            $query = SupportTicket::where('user_id', $request->user()->id)
                ->with(['seller:id,store_name'])
                ->withCount('messages');

            if ($request->filled('status'))     $query->where('status', $request->status);
            if ($request->filled('department')) $query->where('department', $request->department);

            $tickets = $query->latest()
                ->paginate(min($request->integer('per_page', 15), 100));

            return $this->success($tickets, 'Tickets fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch tickets', 500);
        }
    }

    /**
     * Create a ticket
     *
     * Opens a new support ticket. If an `order_id` is provided with department `order`,
     * the ticket is automatically assigned to the seller associated with that order.
     *
     * @bodyParam department string required Department slug from active departments. Example: order
     * @bodyParam subject string required Ticket subject (max 255 chars). Example: Missing game key after purchase
     * @bodyParam message string required Initial message body (max 5000 chars). Example: I purchased Order #000012 but never received my activation key. Please help.
     * @bodyParam priority string Priority level: low, medium, high, urgent. Defaults to medium. Example: high
     * @bodyParam order_id int Order ID to link (must belong to authenticated user). Example: 12
     *
     * @response 201 {
     *   "status": true,
     *   "message": "Ticket created successfully.",
     *   "data": {
     *     "id": 1,
     *     "ticket_number": "TKT-000001",
     *     "subject": "Missing game key after purchase",
     *     "department": "order",
     *     "priority": "high",
     *     "status": "awaiting_seller",
     *     "seller_id": 3,
     *     "order_id": 12,
     *     "created_at": "2026-02-28T12:00:00.000000Z"
     *   }
     * }
     * @response 422 {
     *   "message": "The department field is required.",
     *   "errors": { "department": ["The department field is required."] }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validDepts = implode(',', SupportTicket::departmentSlugs());

        $request->validate([
            'department' => 'required|in:' . $validDepts,
            'subject'    => 'required|string|max:255',
            'message'    => 'required|string|max:5000',
            'priority'   => 'sometimes|in:' . implode(',', SupportTicket::PRIORITIES),
            'order_id'   => 'nullable|exists:orders,id,user_id,' . $request->user()->id,
        ]);

        try {
            $ticket = DB::transaction(function () use ($request) {
                $sellerId = null;
                $status = 'awaiting_admin';

                if ($request->department === 'order' && $request->order_id) {
                    $orderItem = OrderItem::where('order_id', $request->order_id)->first();
                    if ($orderItem && $orderItem->seller_id) {
                        $sellerId = $orderItem->seller_id;
                        $status = 'awaiting_seller';
                    }
                }

                $ticket = SupportTicket::create([
                    'user_id'       => $request->user()->id,
                    'order_id'      => $request->order_id,
                    'seller_id'     => $sellerId,
                    'department'    => $request->department,
                    'subject'       => $request->subject,
                    'priority'      => $request->input('priority', 'medium'),
                    'status'        => $status,
                    'ip_address'    => $request->ip(),
                    'last_reply_at' => now(),
                ]);

                $ticket->messages()->create([
                    'user_id'     => $request->user()->id,
                    'sender_role' => 'customer',
                    'message'     => $request->message,
                ]);

                return $ticket;
            });

            try {
                TicketNotificationService::ticketCreated($ticket);
            } catch (\Throwable $e) {
                report($e);
            }

            return $this->success($ticket, 'Ticket created successfully.', 201);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to create ticket', 500);
        }
    }

    /**
     * View a ticket
     *
     * Returns ticket details with the full conversation thread.
     * Internal notes are not visible to customers.
     *
     * @urlParam id int required The ticket ID. Example: 1
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Ticket fetched successfully",
     *   "data": {
     *     "id": 1,
     *     "ticket_number": "TKT-000001",
     *     "subject": "Missing game key",
     *     "department": "order",
     *     "priority": "high",
     *     "status": "awaiting_seller",
     *     "is_escalated": false,
     *     "created_at": "2026-02-28T12:00:00.000000Z",
     *     "seller": { "id": 1, "store_name": "GameKeys Pro" },
     *     "order": { "id": 12, "order_number": "000012" },
     *     "messages": [
     *       {
     *         "id": 1,
     *         "sender_role": "customer",
     *         "message": "I never received my key.",
     *         "attachments": null,
     *         "created_at": "2026-02-28T12:00:00.000000Z",
     *         "user": { "id": 5, "name": "John Doe" }
     *       }
     *     ]
     *   }
     * }
     * @response 404 {
     *   "status": false,
     *   "message": "Ticket not found."
     * }
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $ticket = SupportTicket::where('user_id', $request->user()->id)
                ->with([
                    'seller:id,store_name',
                    'order:id,order_number',
                    'messages' => fn ($q) => $q->public()->with('user:id,name')->orderBy('created_at'),
                ])
                ->find($id);

            if (!$ticket) {
                return $this->error('Ticket not found.', 404);
            }

            return $this->success($ticket, 'Ticket fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch ticket', 500);
        }
    }

    /**
     * Reply to a ticket
     *
     * Adds a new message to an existing ticket. Only allowed if the ticket is not closed.
     *
     * @urlParam id int required The ticket ID. Example: 1
     * @bodyParam message string required Reply message (max 5000 chars). Example: Could you please check the order again?
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Reply sent.",
     *   "data": {
     *     "id": 4,
     *     "sender_role": "customer",
     *     "message": "Could you please check the order again?",
     *     "created_at": "2026-02-28T15:00:00.000000Z"
     *   }
     * }
     * @response 403 {
     *   "status": false,
     *   "message": "This ticket is closed."
     * }
     */
    public function reply(Request $request, int $id): JsonResponse
    {
        $request->validate(['message' => 'required|string|max:5000']);

        try {
            $ticket = SupportTicket::where('user_id', $request->user()->id)->find($id);

            if (!$ticket) {
                return $this->error('Ticket not found.', 404);
            }

            if ($ticket->status === 'closed') {
                return $this->error('This ticket is closed. You cannot reply to a closed ticket.', 422);
            }

            $msg = DB::transaction(function () use ($request, $ticket) {
                $msg = $ticket->messages()->create([
                    'user_id'     => $request->user()->id,
                    'sender_role' => 'customer',
                    'message'     => $request->message,
                ]);

                $newStatus = $ticket->seller_id && !$ticket->is_escalated
                    ? 'awaiting_seller'
                    : 'awaiting_admin';

                $ticket->update([
                    'status'        => $newStatus,
                    'last_reply_at' => now(),
                ]);

                return $msg;
            });

            try {
                TicketNotificationService::customerReplied($ticket, $msg);
            } catch (\Throwable $e) {
                report($e);
            }

            return $this->success($msg, 'Reply sent.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to send reply', 500);
        }
    }

    /**
     * Escalate to admin
     *
     * Escalates a seller-assigned ticket to the platform admin.
     * Only possible when the ticket is currently assigned to a seller and not already escalated.
     *
     * @urlParam id int required The ticket ID. Example: 1
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Ticket escalated to admin."
     * }
     * @response 400 {
     *   "status": false,
     *   "message": "This ticket cannot be escalated."
     * }
     */
    public function escalate(Request $request, int $id): JsonResponse
    {
        $ticket = SupportTicket::where('user_id', $request->user()->id)->find($id);

        if (!$ticket) {
            return $this->error('Ticket not found.', 404);
        }

        if (!$ticket->seller_id || $ticket->is_escalated || $ticket->status === 'closed') {
            return $this->error('This ticket cannot be escalated.', 422);
        }

        try {
            $ticket->update([
                'is_escalated' => true,
                'escalated_at' => now(),
                'status'       => 'escalated',
            ]);

            try {
                TicketNotificationService::escalated($ticket);
            } catch (\Throwable $e) {
                report($e);
            }

            return $this->success(null, 'Ticket escalated to admin.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to escalate ticket', 500);
        }
    }

    /**
     * Close a ticket
     *
     * Customer can close their own ticket at any time (except already closed tickets).
     *
     * @urlParam id int required The ticket ID. Example: 1
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Ticket closed."
     * }
     */
    public function close(Request $request, int $id): JsonResponse
    {
        $ticket = SupportTicket::where('user_id', $request->user()->id)->find($id);

        if (!$ticket) {
            return $this->error('Ticket not found.', 404);
        }

        if ($ticket->status === 'closed') {
            return $this->error('Ticket is already closed.', 422);
        }

        try {
            $oldStatus = $ticket->status;
            $ticket->update([
                'status'    => 'closed',
                'closed_at' => now(),
            ]);

            try {
                TicketNotificationService::statusChanged($ticket, 'closed', $oldStatus);
            } catch (\Throwable $e) {
                report($e);
            }

            return $this->success(null, 'Ticket closed.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to close ticket', 500);
        }
    }
}
