<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\TicketNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Seller Tickets
 *
 * Manage support tickets assigned to the authenticated seller.
 * Sellers can view tickets from their customers, reply to them,
 * and add internal notes visible only to staff.
 *
 * @authenticated
 */
class SellerTicketController extends Controller
{
    /**
     * List seller's tickets
     *
     * Returns paginated tickets assigned to the authenticated seller's store.
     *
     * @queryParam status string Filter by status. Example: awaiting_seller
     * @queryParam priority string Filter by priority. Example: high
     * @queryParam per_page int Items per page (default 15). Example: 15
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Tickets fetched",
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
     *         "user": { "id": 5, "name": "John Doe" },
     *         "order": { "id": 12, "order_number": "000012" },
     *         "messages_count": 3
     *       }
     *     ],
     *     "total": 2
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $seller = $request->user()->seller;

            if (!$seller) {
                return $this->error('Seller profile not found.', 403);
            }

            $query = SupportTicket::where('seller_id', $seller->id)
                ->where('is_escalated', false)
                ->with(['user:id,name,email', 'order:id,order_number'])
                ->withCount('messages');

            if ($request->filled('status'))   $query->where('status', $request->status);
            if ($request->filled('priority')) $query->where('priority', $request->priority);

            $tickets = $query->latest()->paginate(min($request->integer('per_page', 15), 50));

            return $this->success($tickets, 'Tickets fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * View a ticket
     *
     * Returns ticket details with the full conversation visible to the seller.
     * Internal admin notes are hidden from seller view. Escalated tickets are not accessible.
     *
     * @urlParam id int required The ticket ID. Example: 1
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Ticket details fetched",
     *   "data": {
     *     "id": 1,
     *     "ticket_number": "TKT-000001",
     *     "subject": "Missing game key",
     *     "department": "order",
     *     "priority": "high",
     *     "status": "awaiting_seller",
     *     "user": { "id": 5, "name": "John Doe", "email": "john@example.com" },
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
            $seller = $request->user()->seller;

            if (!$seller) {
                return $this->error('Seller profile not found.', 403);
            }

            $ticket = SupportTicket::where('seller_id', $seller->id)
                ->where('is_escalated', false)
                ->with([
                    'user:id,name,email',
                    'order:id,order_number',
                    'messages' => fn ($q) => $q->where(function ($q2) use ($request) {
                        $q2->where('is_internal_note', false)
                           ->orWhere(fn ($q3) => $q3->where('sender_role', 'seller')->where('user_id', $request->user()->id));
                    })->with('user:id,name')->orderBy('created_at'),
                ])
                ->find($id);

            if (!$ticket) {
                return $this->error('Ticket not found.', 404);
            }

            return $this->success($ticket, 'Ticket details fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Reply to a ticket
     *
     * Adds a seller reply to the ticket conversation.
     * Optionally, the seller can add an internal note visible only to staff.
     * Cannot reply to closed or escalated tickets.
     *
     * @urlParam id int required The ticket ID. Example: 1
     * @bodyParam message string required Reply message (max 5000 chars). Example: I have resent the activation key to your email.
     * @bodyParam is_internal_note boolean Whether this is an internal note (default false). Example: false
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Reply sent.",
     *   "data": {
     *     "id": 5,
     *     "sender_role": "seller",
     *     "message": "I have resent the activation key to your email.",
     *     "is_internal_note": false,
     *     "created_at": "2026-02-28T16:00:00.000000Z"
     *   }
     * }
     * @response 403 {
     *   "status": false,
     *   "message": "This ticket is closed."
     * }
     */
    public function reply(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'message'          => 'required|string|max:5000',
            'is_internal_note' => 'sometimes|boolean',
        ]);

        try {
            $seller = $request->user()->seller;

            if (!$seller) {
                return $this->error('Seller profile not found.', 403);
            }

            $ticket = SupportTicket::where('seller_id', $seller->id)
                ->where('is_escalated', false)
                ->find($id);

            if (!$ticket) {
                return $this->error('Ticket not found.', 404);
            }

            if ($ticket->status === 'closed') {
                return $this->error('This ticket is closed. You cannot reply to a closed ticket.', 422);
            }

            $isNote = $request->boolean('is_internal_note');

            $msg = DB::transaction(function () use ($ticket, $request, $isNote) {
                $msg = $ticket->messages()->create([
                    'user_id'          => $request->user()->id,
                    'sender_role'      => 'seller',
                    'message'          => $request->message,
                    'is_internal_note' => $isNote,
                ]);

                if (!$isNote) {
                    $ticket->update([
                        'status'        => 'awaiting_customer',
                        'last_reply_at' => now(),
                    ]);
                }

                return $msg;
            });

            if (!$isNote) {
                try {
                    TicketNotificationService::staffReplied($ticket, $msg);
                } catch (\Throwable $e) {
                    \Log::warning('Ticket reply notification failed: ' . $e->getMessage());
                }
            }

            return $this->success($msg, 'Reply sent.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }
}
