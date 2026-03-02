<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\TicketDepartment;
use App\Models\CannedResponse;
use App\Services\TicketNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class SupportTicketController extends Controller
{
    protected function storeAttachments(array $files, int $ticketId): array
    {
        $dir = storage_path('app/public/tickets/' . $ticketId);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $attachments = [];
        foreach ($files as $file) {
            $name = now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $name);
            $attachments[] = '/storage/tickets/' . $ticketId . '/' . $name;
        }

        return $attachments;
    }

    public function create()
    {
        $customers = User::whereHas('roles', fn ($q) => $q->where('name', 'customer'))
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        $departments = TicketDepartment::active()->orderBy('sort_order')->get();

        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $sellers = \App\Models\Seller::where('status', 'active')
            ->select('id', 'store_name', 'user_id')
            ->orderBy('store_name')
            ->get();

        return view('content.support-tickets.create', compact(
            'customers', 'departments', 'admins', 'sellers'
        ));
    }

    public function store(Request $request)
    {
        $validDepts = implode(',', SupportTicket::departmentSlugs());

        $data = $request->validate([
            'user_id'           => 'required|exists:users,id',
            'subject'           => 'required|string|max:255',
            'department'        => 'required|in:' . $validDepts,
            'priority'          => 'required|in:' . implode(',', SupportTicket::PRIORITIES),
            'status'            => 'nullable|in:' . implode(',', SupportTicket::STATUSES),
            'order_id'          => 'nullable|exists:orders,id',
            'seller_id'         => 'nullable|exists:sellers,id',
            'assigned_admin_id' => 'nullable|exists:users,id',
            'message'           => 'required|string|max:10000',
            'attachments.*'     => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,bmp,pdf,doc,docx,zip,rar,txt',
        ]);

        $ticket = DB::transaction(function () use ($data, $request) {
            $ticket = SupportTicket::create([
                'user_id'           => $data['user_id'],
                'subject'           => $data['subject'],
                'department'        => $data['department'],
                'priority'          => $data['priority'],
                'status'            => $data['status'] ?? 'open',
                'order_id'          => $data['order_id'] ?? null,
                'seller_id'         => $data['seller_id'] ?? null,
                'assigned_admin_id' => $data['assigned_admin_id'] ?? null,
                'ip_address'        => $request->ip(),
            ]);

            $attachments = $request->hasFile('attachments')
                ? $this->storeAttachments($request->file('attachments'), $ticket->id)
                : [];

            $ticket->messages()->create([
                'user_id'     => auth()->id(),
                'sender_role' => 'admin',
                'message'     => $data['message'],
                'attachments' => $attachments ?: null,
            ]);

            return $ticket;
        });

        TicketNotificationService::ticketCreated($ticket);

        return redirect()->route('support-tickets.show', $ticket)
            ->with('success', 'Ticket ' . $ticket->ticket_number . ' created successfully.');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = SupportTicket::with(['user', 'seller', 'assignedAdmin']);

            if ($request->filled('status'))     $query->where('status', $request->status);
            if ($request->filled('department'))  $query->where('department', $request->department);
            if ($request->filled('priority'))    $query->where('priority', $request->priority);

            $departments = TicketDepartment::pluck('color', 'slug')->toArray();

            return DataTables::of($query)
                ->addColumn('checkbox', fn ($r) =>
                    '<input type="checkbox" class="form-check-input row-checkbox" value="' . $r->id . '">')
                ->addColumn('ticket_col', function ($r) {
                    $url = route('support-tickets.show', $r->id);
                    return '<a href="' . $url . '" class="fw-semibold text-primary">' . e($r->ticket_number) . '</a>'
                        . '<br><small class="text-muted">' . e(Str::limit($r->subject, 40)) . '</small>';
                })
                ->addColumn('customer_col', function ($r) {
                    $name = e($r->user->name ?? '—');
                    $email = e($r->user->email ?? '');
                    return '<span class="fw-semibold">' . $name . '</span><br><small class="text-muted">' . $email . '</small>';
                })
                ->addColumn('department_badge', function ($r) use ($departments) {
                    $color = $departments[$r->department] ?? 'secondary';
                    return '<span class="badge bg-label-' . $color . '">' . ucfirst($r->department) . '</span>';
                })
                ->addColumn('priority_badge', function ($r) {
                    $map = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'];
                    $color = $map[$r->priority] ?? 'secondary';
                    return '<span class="badge bg-label-' . $color . '">' . ucfirst($r->priority) . '</span>';
                })
                ->addColumn('status_badge', function ($r) {
                    $map = [
                        'open'              => 'primary',
                        'awaiting_seller'   => 'warning',
                        'awaiting_admin'    => 'info',
                        'awaiting_customer' => 'dark',
                        'on_hold'           => 'secondary',
                        'escalated'         => 'danger',
                        'resolved'          => 'success',
                        'closed'            => 'secondary',
                    ];
                    $color = $map[$r->status] ?? 'secondary';
                    $label = ucwords(str_replace('_', ' ', $r->status));
                    return '<span class="badge bg-label-' . $color . '">' . $label . '</span>';
                })
                ->addColumn('assigned_col', function ($r) {
                    if ($r->seller) return '<small><i class="ti tabler-building-store me-1"></i>' . e($r->seller->store_name) . '</small>';
                    if ($r->assignedAdmin) return '<small><i class="ti tabler-shield me-1"></i>' . e($r->assignedAdmin->name) . '</small>';
                    return '<small class="text-muted">Unassigned</small>';
                })
                ->addColumn('date_col', fn ($r) => $r->created_at?->format('M d, Y H:i') ?? '—')
                ->addColumn('actions', function ($r) {
                    $url = route('support-tickets.show', $r->id);
                    return '<div class="dropdown">
                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <i class="ti tabler-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="' . $url . '" class="dropdown-item"><i class="ti tabler-eye me-1"></i> View</a>
                            <a href="javascript:void(0);" class="dropdown-item btn-close-ticket" data-id="' . $r->id . '"><i class="ti tabler-circle-check me-1"></i> Close</a>
                            <div class="dropdown-divider"></div>
                            <a href="javascript:void(0);" class="dropdown-item text-danger btn-delete" data-id="' . $r->id . '"><i class="ti tabler-trash me-1"></i> Delete</a>
                        </div>
                    </div>';
                })
                ->rawColumns(['checkbox', 'ticket_col', 'customer_col', 'department_badge', 'priority_badge', 'status_badge', 'assigned_col', 'actions'])
                ->make(true);
        }

        $stats = [
            'open'      => SupportTicket::whereNotIn('status', ['resolved', 'closed'])->count(),
            'escalated' => SupportTicket::where('status', 'escalated')->count(),
            'resolved'  => SupportTicket::where('status', 'resolved')->count(),
            'closed'    => SupportTicket::where('status', 'closed')->count(),
        ];

        return view('content.support-tickets.index', compact('stats'));
    }

    public function show(SupportTicket $supportTicket)
    {
        $supportTicket->load([
            'user', 'seller', 'assignedAdmin',
            'order.items.product', 'order.items.seller',
            'messages' => fn ($q) => $q->with('user')->orderBy('created_at', 'asc'),
        ]);

        $admins = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->select('id', 'name')
            ->get();

        $customer = $supportTicket->user;
        $customerStats = null;
        $previousTickets = collect();

        if ($customer) {
            $customerStats = [
                'total_orders'  => Order::where('user_id', $customer->id)->count(),
                'total_spent'   => Order::where('user_id', $customer->id)->where('payment_status', 'paid')->sum('total_amount'),
                'total_tickets' => SupportTicket::where('user_id', $customer->id)->count(),
                'open_tickets'  => SupportTicket::where('user_id', $customer->id)->whereNotIn('status', ['resolved', 'closed'])->count(),
                'member_since'  => $customer->created_at,
            ];

            $previousTickets = SupportTicket::where('user_id', $customer->id)
                ->where('id', '!=', $supportTicket->id)
                ->latest()
                ->limit(5)
                ->get(['id', 'ticket_number', 'subject', 'status', 'priority', 'created_at']);
        }

        $cannedResponses = CannedResponse::active()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get();

        $sla = [
            'created_ago'      => $supportTicket->created_at->diffForHumans(),
            'first_reply_mins' => null,
            'resolution_mins'  => null,
        ];

        $firstStaffReply = $supportTicket->messages
            ->where('sender_role', '!=', 'customer')
            ->where('is_internal_note', false)
            ->first();

        if ($firstStaffReply) {
            $sla['first_reply_mins'] = round($supportTicket->created_at->diffInMinutes($firstStaffReply->created_at));
        }

        if ($supportTicket->resolved_at) {
            $sla['resolution_mins'] = round($supportTicket->created_at->diffInMinutes($supportTicket->resolved_at));
        }

        return view('content.support-tickets.show', compact(
            'supportTicket', 'admins', 'customerStats', 'previousTickets',
            'cannedResponses', 'sla'
        ));
    }

    public function reply(Request $request, SupportTicket $supportTicket)
    {
        $request->validate([
            'message'          => 'required|string|max:5000',
            'is_internal_note' => 'sometimes|boolean',
            'attachments.*'    => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,bmp,pdf,doc,docx,zip,rar,txt',
        ]);

        $attachments = $request->hasFile('attachments')
            ? $this->storeAttachments($request->file('attachments'), $supportTicket->id)
            : [];

        $isNote = $request->boolean('is_internal_note');

        $message = $supportTicket->messages()->create([
            'user_id'          => auth()->id(),
            'sender_role'      => 'admin',
            'message'          => $request->message,
            'attachments'      => $attachments ?: null,
            'is_internal_note' => $isNote,
        ]);

        if (!$isNote) {
            $supportTicket->update([
                'status'        => 'awaiting_customer',
                'last_reply_at' => now(),
            ]);

            TicketNotificationService::staffReplied($supportTicket, $message);
        }

        $msg = $isNote ? 'Internal note added.' : 'Reply sent successfully.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    public function updateStatus(Request $request, SupportTicket $supportTicket)
    {
        $request->validate(['status' => 'required|in:' . implode(',', SupportTicket::STATUSES)]);

        if (!$supportTicket->canTransitionTo($request->status)) {
            return response()->json([
                'message' => 'Cannot transition from ' . str_replace('_', ' ', $supportTicket->status)
                    . ' to ' . str_replace('_', ' ', $request->status) . '.',
            ], 422);
        }

        $oldStatus = $supportTicket->status;
        $data = ['status' => $request->status];

        if ($request->status === 'resolved') {
            $data['resolved_at'] = now();
        } elseif ($request->status === 'closed') {
            $data['closed_at'] = now();
        } elseif (in_array($request->status, ['open', 'awaiting_seller', 'awaiting_admin', 'awaiting_customer'])) {
            $data['resolved_at'] = null;
            $data['closed_at'] = null;
            if ($request->status !== 'escalated') {
                $data['is_escalated'] = false;
            }
        }

        $supportTicket->update($data);

        TicketNotificationService::statusChanged($supportTicket, $request->status, $oldStatus);

        return response()->json(['message' => 'Status updated to ' . ucwords(str_replace('_', ' ', $request->status)) . '.']);
    }

    public function updatePriority(Request $request, SupportTicket $supportTicket)
    {
        $request->validate(['priority' => 'required|in:' . implode(',', SupportTicket::PRIORITIES)]);

        $supportTicket->update(['priority' => $request->priority]);

        return response()->json(['message' => 'Priority updated to ' . ucfirst($request->priority) . '.']);
    }

    public function assign(Request $request, SupportTicket $supportTicket)
    {
        $request->validate(['admin_id' => 'required|exists:users,id']);

        $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->where('id', $request->admin_id)
            ->first();

        if (!$admin) {
            return response()->json(['message' => 'Selected user is not an admin.'], 422);
        }

        $supportTicket->update([
            'assigned_admin_id' => $admin->id,
            'status'            => 'awaiting_admin',
        ]);

        $supportTicket->refresh();
        TicketNotificationService::assigned($supportTicket);

        return response()->json(['message' => 'Ticket assigned successfully.']);
    }

    public function escalate(SupportTicket $supportTicket)
    {
        if ($supportTicket->is_escalated) {
            return response()->json(['message' => 'Ticket is already escalated.'], 422);
        }

        $supportTicket->update([
            'is_escalated'  => true,
            'escalated_at'  => now(),
            'status'        => 'escalated',
        ]);

        TicketNotificationService::escalated($supportTicket);

        return response()->json(['message' => 'Ticket escalated to admin.']);
    }

    public function destroy(SupportTicket $supportTicket)
    {

        $dir = storage_path('app/public/tickets/' . $supportTicket->id);
        if (File::isDirectory($dir)) {
            File::deleteDirectory($dir);
        }

        $supportTicket->messages()->delete();
        $supportTicket->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Ticket deleted successfully.']);
        }
        return redirect()->route('support-tickets.index')->with('success', 'Ticket deleted.');
    }

    public function bulkDelete(Request $request)
    {

        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:support_tickets,id',
        ]);

        foreach ($request->ids as $id) {
            $dir = storage_path('app/public/tickets/' . $id);
            if (File::isDirectory($dir)) {
                File::deleteDirectory($dir);
            }
        }

        DB::table('support_ticket_messages')->whereIn('ticket_id', $request->ids)->delete();
        SupportTicket::whereIn('id', $request->ids)->delete();

        return response()->json(['message' => count($request->ids) . ' tickets deleted.']);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids'      => 'required|array|min:1',
            'ids.*'    => 'integer|exists:support_tickets,id',
            'action'   => 'required|in:close,change_status,change_priority,assign',
            'status'   => 'required_if:action,change_status|in:' . implode(',', SupportTicket::STATUSES),
            'priority' => 'required_if:action,change_priority|in:' . implode(',', SupportTicket::PRIORITIES),
            'admin_id' => 'required_if:action,assign|exists:users,id',
        ]);

        $ticketModels = SupportTicket::whereIn('id', $request->ids)->get();
        $count = $ticketModels->count();

        switch ($request->action) {
            case 'close':
                foreach ($ticketModels as $ticket) {
                    $oldStatus = $ticket->status;
                    $ticket->update(['status' => 'closed', 'closed_at' => now()]);
                    try { TicketNotificationService::statusChanged($ticket, 'closed', $oldStatus); } catch (\Throwable $e) { report($e); }
                }
                $label = "$count tickets closed.";
                break;

            case 'change_status':
                foreach ($ticketModels as $ticket) {
                    $oldStatus = $ticket->status;
                    $data = ['status' => $request->status];
                    if ($request->status === 'resolved') $data['resolved_at'] = now();
                    if ($request->status === 'closed')   $data['closed_at'] = now();
                    if (in_array($request->status, ['open', 'awaiting_seller', 'awaiting_admin', 'awaiting_customer'])) {
                        $data['resolved_at'] = null;
                        $data['closed_at'] = null;
                    }
                    $ticket->update($data);
                    try { TicketNotificationService::statusChanged($ticket, $request->status, $oldStatus); } catch (\Throwable $e) { report($e); }
                }
                $label = "$count tickets updated to " . ucwords(str_replace('_', ' ', $request->status)) . ".";
                break;

            case 'change_priority':
                SupportTicket::whereIn('id', $request->ids)->update(['priority' => $request->priority]);
                $label = "$count tickets set to " . ucfirst($request->priority) . " priority.";
                break;

            case 'assign':
                $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))
                    ->where('id', $request->admin_id)->first();
                if (!$admin) {
                    return response()->json(['message' => 'Selected user is not an admin.'], 422);
                }
                foreach ($ticketModels as $ticket) {
                    $ticket->update(['assigned_admin_id' => $admin->id, 'status' => 'awaiting_admin']);
                    try { TicketNotificationService::assigned($ticket); } catch (\Throwable $e) { report($e); }
                }
                $label = "$count tickets assigned to {$admin->name}.";
                break;

            default:
                return response()->json(['message' => 'Invalid action.'], 422);
        }

        return response()->json(['message' => $label]);
    }
}
