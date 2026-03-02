<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\SellerOffer;
use App\Models\Transaction;
use App\Models\OrderAddress;
use Illuminate\Http\Request;
use App\Models\OrderDelivery;
use App\Models\PaymentMethod;
use App\Models\SellerEarning;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Events\OrderCancelled;
use App\Events\OrderRefunded;
use App\Jobs\AutoDeliverOrderJob;
use App\Services\OrderDeliveryService;
use App\Services\OrderNotificationService;
use App\Services\InvoiceService;
use App\Services\SellerBalanceService;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $orders = Order::query()
                ->with([
                    'user:id,name,email',
                    'transactions' => function ($q) {
                        $q->latest()->limit(1);
                    }
                ])
                ->select([
                    'id',
                    'user_id',
                    'order_number',
                    'status',
                    'total_amount',
                    'base_total_amount',
                    'payment_method',
                    'payment_status',
                    'currency',
                    'base_currency',
                    'exchange_rate',
                    'created_at'
                ]);

            if ($request->filled('status')) $orders->where('status', $request->status);
            if ($request->filled('payment_status')) $orders->where('payment_status', $request->payment_status);
            if ($request->filled('payment_method')) $orders->where('payment_method', $request->payment_method);

            if ($request->filled('filters')) {
                $this->applyFilters($orders, $request->filters);
            }

            return DataTables::of($orders)
                ->addIndexColumn()

                ->addColumn('checkbox', fn($order) =>
                    '<input type="checkbox" class="form-check-input bulk-checkbox" value="'.$order->id.'">'
                )

                ->addColumn('order_col', function ($order) {
                    $no = $order->order_number ?? $order->id;
                    $date = $order->created_at?->format('M d, Y') ?? '-';
                    return '<a href="'.route('orders.edit', $order->id).'" class="fw-semibold text-body">#'.$no.'</a>'
                         . '<div class="text-muted small">'.$date.'</div>';
                })

                ->addColumn('buyer', function ($order) {
                    if (!$order->user) {
                        return '<span class="text-muted">Guest</span>';
                    }
                    $avatar = $order->user->avatar_url ?? asset('assets/img/avatars/1.png');
                    return '<div class="d-flex align-items-center">'
                         . '<img src="'.$avatar.'" class="rounded-circle me-3" width="38" height="38" style="object-fit:cover">'
                         . '<div class="lh-sm">'
                         . '<span class="fw-semibold">'.e($order->user->name).'</span>'
                         . '<div class="text-muted small">'.e($order->user->email).'</div>'
                         . '</div></div>';
                })

                ->addColumn('payment_method', function ($order) {
                    $method = $order->payment_method;
                    if (!$method) {
                        return '<span class="text-muted">—</span>';
                    }
                    $icons = ['wallet' => 'tabler-wallet', 'stripe' => 'tabler-brand-stripe', 'paypal' => 'tabler-brand-paypal'];
                    $icon = $icons[strtolower($method)] ?? 'tabler-credit-card';
                    return '<div class="d-flex align-items-center"><i class="ti '.$icon.' me-2 ti-sm text-muted"></i><span>'.ucfirst($method).'</span></div>';
                })

                ->addColumn('total_formatted', function ($order) {
                    $formatted = format_currency($order->total_amount, $order->currency);
                    $baseCurrency = $order->base_currency ?? $order->currency;
                    if ($order->currency !== $baseCurrency && $order->exchange_rate && $order->exchange_rate != 1) {
                        $baseFormatted = format_currency($order->base_total_amount ?? $order->total_amount);
                        return '<span class="fw-semibold">'.$formatted.'</span>'
                             . '<div class="text-muted" style="font-size:.7rem">'.$baseFormatted.'</div>';
                    }
                    return '<span class="fw-semibold">'.$formatted.'</span>';
                })

                ->addColumn('status_badge', function ($order) {
                    $map = [
                        'pending'    => 'warning',
                        'processing' => 'info',
                        'completed'  => 'success',
                        'refunded'   => 'secondary',
                        'cancelled'  => 'danger',
                    ];
                    $color = $map[$order->status] ?? 'secondary';
                    return '<span class="badge bg-label-'.$color.'">'.ucfirst($order->status).'</span>';
                })

                ->addColumn('payment_status', function ($order) {
                    $map = [
                        'pending'  => 'warning',
                        'paid'     => 'success',
                        'failed'   => 'danger',
                        'refunded' => 'secondary',
                    ];
                    $status = $order->payment_status ?? 'pending';
                    $color = $map[$status] ?? 'secondary';
                    return '<span class="badge bg-label-'.$color.'">'.ucfirst($status).'</span>';
                })

                ->addColumn('actions', fn($order) =>
                    view('content.orders.partials.actions', ['order' => $order])->render()
                )

                ->rawColumns([
                    'checkbox', 'order_col', 'buyer', 'status_badge',
                    'payment_status', 'payment_method', 'total_formatted', 'actions'
                ])
                ->make(true);
        }

        $stats = [
            'total'      => Order::count(),
            'pending'    => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed'  => Order::where('status', 'completed')->count(),
            'cancelled'  => Order::where('status', 'cancelled')->count(),
            'revenue'    => Order::where('payment_status', 'paid')->sum('total_amount'),
        ];

        return view('content.orders.index', compact('stats'));
    }

    public function create()
    {
        $users = User::all();
        $paymentMethods = PaymentMethod::all();
        $products = Product::active()->get();

        return view('content.orders.create', compact('users', 'paymentMethods', 'products'));
    }

    /**
     * Store manually created admin order.
     */
    public function store(Request $request)
    {

        $request->validate([
            'buyer_id'                     => ['required', 'exists:users,id'],
            'items'                        => ['required', 'array', 'min:1'],
            'items.*.seller_offer_id'      => ['required', 'exists:seller_offers,id'],
            'items.*.quantity'             => ['required', 'integer', 'min:1'],

            'billing.name'                 => ['required', 'string'],
            'billing.email'                => ['nullable', 'email'],
            'billing.phone'                => ['nullable', 'string'],
            'billing.country'              => ['required', 'string'],
            'billing.address'              => ['required', 'string'],
            'billing.city'                 => ['required', 'string'],
            'billing.postcode'             => ['nullable', 'string'],

            'payment_method'               => ['required', 'string'],
            'payment_ref'                  => ['nullable', 'string'],
            'status'                       => ['required', 'string', 'in:pending,processing,completed'],
            'fees'                         => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        DB::transaction(function () use ($request) {

            $buyer = User::findOrFail($request->buyer_id);

            $subtotal = 0;
            $orderItems = [];

            foreach ($request->items as $item) {

                $offer = SellerOffer::lockForUpdate()->findOrFail($item['seller_offer_id']);

                if ($offer->status !== 'active') {
                    abort(422, 'One or more seller offers are inactive.');
                }

                $unitPrice = $offer->retail_price;
                $lineTotal = bcmul($unitPrice, $item['quantity'], 2);

                $subtotal = bcadd($subtotal, $lineTotal, 2);

                $orderItems[] = [
                    'offer'     => $offer,
                    'quantity'  => $item['quantity'],
                    'unitPrice' => $unitPrice,
                    'lineTotal' => $lineTotal,
                ];
            }

            $fees            = $request->input('fees', 0);
            $taxAmount       = 0;
            $discountAmount  = 0;

            $totalAmount = bcadd(
                bcadd($subtotal, $fees, 2),
                -$discountAmount,
                2
            );

            $defaultCurrency = \App\Models\Setting::get('general', 'default_currency', 'USD');

            $order = Order::create([
                'user_id'              => $buyer->id,
                'currency'             => $defaultCurrency,
                'base_currency'        => $defaultCurrency,
                'base_subtotal'        => $subtotal,
                'base_tax_amount'      => $taxAmount,
                'base_discount_amount' => $discountAmount,
                'base_total_amount'    => $totalAmount,
                'exchange_rate'        => 1.00000000,

                'subtotal'         => $subtotal,
                'tax_amount'       => $taxAmount,
                'discount_amount'  => $discountAmount,
                'total_amount'     => $totalAmount,

                'payment_method'   => $request->payment_method,
                'payment_reference' => $request->payment_ref,
                'payment_status'   => 'paid',

                'status'           => $request->status,
                'paid_at'          => now(),

                'meta' => [
                    'created_by' => [
                        'type' => 'admin',
                        'id'   => auth()->id(),
                    ],
                    'flags' => [
                        'manual_order' => true,
                        'auto_delivery' => true,
                    ],
                ],
            ]);

            OrderAddress::create([
                'order_id' => $order->id,
                'type'     => 'billing',
                'name'     => $request->input('billing.name'),
                'email'    => $request->input('billing.email'),
                'phone'    => $request->input('billing.phone'),
                'country'  => $request->input('billing.country'),
                'address'  => $request->input('billing.address'),
                'city'     => $request->input('billing.city'),
                'postcode' => $request->input('billing.postcode'),
            ]);

            foreach ($orderItems as $data) {

                $offer = $data['offer'];

                $orderItem = OrderItem::create([
                    'order_id'        => $order->id,
                    'seller_id'       => $offer->seller_id,
                    'product_id'      => $offer->product_id,
                    'seller_offer_id' => $offer->id,
                    'quantity'        => $data['quantity'],
                    'unit_price'      => $data['unitPrice'],
                    'subtotal'        => $data['lineTotal'],
                    'delivery_type'   => 'auto',
                    'delivery_status' => 'pending',
                    'status'          => 'active',
                ]);

                OrderDelivery::create([
                    'order_item_id'   => $orderItem->id,
                    'delivery_method' => 'auto',
                    'status'          => 'pending',
                ]);

                $commissionRate = SellerBalanceService::getCommissionRate($offer->product);
                $commission = round($data['lineTotal'] * ($commissionRate / 100), 2);
                $netAmount  = bcsub($data['lineTotal'], $commission, 2);

                SellerEarning::create([
                    'seller_id'     => $offer->seller_id,
                    'order_id'      => $order->id,
                    'order_item_id' => $orderItem->id,
                    'gross_amount'  => $data['lineTotal'],
                    'commission'    => $commission,
                    'net_amount'    => $netAmount,
                    'status'        => 'pending',
                ]);
            }

            Transaction::create([
                'user_id'         => $buyer->id,
                'reference_type'  => Order::class,
                'reference_id'    => $order->id,
                'trx'             => 'TRX-' . now()->format('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'amount'          => $order->total_amount,
                'fee'             => 0,
                'net_amount'      => $order->total_amount,
                'currency'        => $order->currency,
                'type'            => 'debit',
                'category'        => 'order',
                'status'          => 'completed',
                'payment_method'  => $request->payment_method,
            ]);

            // Admin-created orders are marked 'paid' — update seller balances
            app(SellerBalanceService::class)->onPaymentConfirmed($order->id);

            $order->notes()->create([
                'user_id' => auth()->id(),
                'note'    => 'Order created manually by admin.',
                'type'    => 'system',
                'is_visible_to_customer' => false,
            ]);

            OrderNotificationService::orderPlaced($order);
            if ($order->payment_status === 'paid') {
                OrderNotificationService::paymentConfirmed($order);
                app(InvoiceService::class)->generateFromOrder($order);
                dispatch(new AutoDeliverOrderJob($order->id));
            }
        });

        return redirect()
            ->route('orders.index')
            ->with('success', 'Order created successfully.');
    }

    /**
     * Show a single order (redirects to edit which serves as the detail page).
     */
    public function show(int $id)
    {
        return redirect()->route('orders.edit', $id);
    }

    public function edit(int $id)
    {
        $order = Order::with([
            'user',
            'items.product:id,title,image',
            'items.seller:id,store_name',
            'items.deliveries',
            'items.earning',
            'addresses',
            'transactions' => fn ($q) => $q->latest(),
            'notes' => fn ($q) => $q->with('user:id,name')->latest(),
            'invoice',
        ])->findOrFail($id);

        $billingAddress  = $order->addresses->firstWhere('type', 'billing');
        $shippingAddress = $order->addresses->firstWhere('type', 'shipping');

        // Seller earnings summary for this order
        $sellerSummary = SellerEarning::where('order_id', $order->id)
            ->selectRaw("
                seller_id,
                SUM(gross_amount) as total_gross,
                SUM(commission) as total_commission,
                SUM(net_amount) as total_net,
                MIN(status) as earning_status
            ")
            ->groupBy('seller_id')
            ->with('seller:id,store_name')
            ->get();

        return view('content.orders.edit', compact(
            'order',
            'billingAddress',
            'shippingAddress',
            'sellerSummary'
        ));
    }

    public function update(Request $request, Order $order)
    {

        $request->validate([
            'status' => 'required|in:pending,processing,completed,refunded,cancelled',
        ]);

        if (in_array($order->status, ['cancelled', 'refunded'])) {
            return back()->with('warning', 'Order can no longer be updated.');
        }

        $allowedTransitions = [
            'pending'    => ['processing', 'cancelled'],
            'processing' => ['completed', 'cancelled', 'refunded'],
            'completed'  => ['refunded'],
        ];

        $allowed = $allowedTransitions[$order->status] ?? [];
        if (!in_array($request->status, $allowed)) {
            return back()->with('warning', "Cannot transition from '{$order->status}' to '{$request->status}'.");
        }

        if (
            in_array($request->status, ['completed', 'refunded']) &&
            $order->payment_status !== 'paid'
        ) {
            return back()->with('warning', 'Order must be paid first.');
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        $order->update(['status' => $newStatus]);

        // ── Processing: ensure seller balances are funded ──
        if ($newStatus === 'processing' && $oldStatus === 'pending') {
            $hasPendingEarnings = SellerEarning::where('order_id', $order->id)
                ->where('status', 'pending')
                ->exists();

            if ($hasPendingEarnings && $order->payment_status === 'paid') {
                app(SellerBalanceService::class)->onPaymentConfirmed($order->id);
            }
        }

        // ── Completed: trigger delivery + release seller balances ──
        if ($newStatus === 'completed') {

            $order->load('items.deliveries');

            foreach ($order->items as $item) {
                foreach ($item->deliveries as $delivery) {
                    if (in_array($delivery->status, ['pending', 'failed'])) {
                        if ($delivery->delivery_method === 'auto') {
                            app(OrderDeliveryService::class)->autoDeliver($delivery);
                        }
                    }
                }
            }

            // Release pending earnings to available balance
            $hasPendingEarnings = SellerEarning::where('order_id', $order->id)
                ->where('status', 'pending')
                ->exists();

            if ($hasPendingEarnings) {
                // First ensure payment is confirmed in balances
                app(SellerBalanceService::class)->onPaymentConfirmed($order->id);
            }

            // Admin-completed orders skip escrow — earnings go straight to available
            app(SellerBalanceService::class)->onOrderCompleted($order->id, escrowDays: 0);

            $order->update(['completed_at' => now()]);

            if ($order->payment_status === 'paid' && !$order->invoice) {
                app(InvoiceService::class)->generateFromOrder($order);
            }
        }

        // ── Cancelled: fire event (listeners revert earnings, refund wallet, restore coupon, notify) ──
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            $order->update(['cancelled_at' => now()]);
            event(new OrderCancelled($order->fresh()));
        }

        // ── Refunded: fire event (listeners revert seller earnings + notify) ──
        if ($newStatus === 'refunded' && $oldStatus !== 'refunded') {
            $order->update(['refunded_at' => now()]);
            event(new OrderRefunded($order->fresh(), (float) $order->total_amount));
        }

        $order->notes()->create([
            'user_id' => auth()->id(),
            'note'    => "Order status changed from {$oldStatus} to {$newStatus} by admin.",
            'type'    => 'system',
            'is_visible_to_customer' => true,
        ]);

        if ($newStatus === 'processing' && $oldStatus !== 'processing') {
            OrderNotificationService::statusChanged($order, 'processing');
        } elseif ($newStatus === 'completed') {
            OrderNotificationService::orderCompleted($order);
        }

        return back()->with('success', 'Order status updated.');
    }

    public function updateBilling(Request $request, Order $order)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'address' => 'required|string',
            'city'    => 'required|string',
            'state'   => 'nullable|string',
            'postal_code' => 'nullable|string',
            'phone'   => 'nullable|string',
            'country' => 'required|string',
        ]);

        $order->addresses()
            ->where('type', 'billing')
            ->update($data);

        return back()->with('success', 'Billing address updated successfully.');
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);

        if (in_array($order->payment_status, ['paid']) && in_array($order->status, ['processing', 'completed'])) {
            return response()->json(['message' => 'Cannot delete a paid/active order. Cancel or refund it first.'], 422);
        }

        $this->revertSellerEarnings($order);
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:orders,id',
        ]);

        $orders = Order::whereIn('id', $request->ids)->get();
        $blocked = $orders->filter(fn ($o) => $o->payment_status === 'paid' && in_array($o->status, ['processing', 'completed']));

        if ($blocked->isNotEmpty()) {
            return response()->json([
                'message' => 'Cannot delete ' . $blocked->count() . ' paid/active order(s). Cancel or refund them first.',
            ], 422);
        }

        foreach ($orders as $order) {
            $this->revertSellerEarnings($order);
            $order->delete();
        }

        return response()->json(['message' => 'Orders deleted successfully']);
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $orders = Order::whereIn('id', $request->ids)->get();
        $balanceService = app(SellerBalanceService::class);

        foreach ($orders as $order) {
            $oldStatus = $order->status;
            $newStatus = $request->status;

            if ($oldStatus === $newStatus || $oldStatus === 'cancelled') {
                continue;
            }

            $order->update(['status' => $newStatus]);

            if ($newStatus === 'completed' && $order->payment_status === 'paid') {
                $order->load('items.deliveries');
                foreach ($order->items as $item) {
                    foreach ($item->deliveries as $delivery) {
                        if (in_array($delivery->status, ['pending', 'failed']) && $delivery->delivery_method === 'auto') {
                            app(OrderDeliveryService::class)->autoDeliver($delivery);
                        }
                    }
                }

                $hasPending = SellerEarning::where('order_id', $order->id)
                    ->where('status', 'pending')
                    ->exists();

                if ($hasPending) {
                    $balanceService->onPaymentConfirmed($order->id);
                }

                $balanceService->onOrderCompleted($order->id, escrowDays: 0);
                $order->update(['completed_at' => now()]);
            }

            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                $order->update(['cancelled_at' => now()]);
                event(new OrderCancelled($order->fresh()));
            }

            if ($newStatus === 'processing' && $oldStatus !== 'processing') {
                OrderNotificationService::statusChanged($order, 'processing');
            } elseif ($newStatus === 'completed') {
                OrderNotificationService::orderCompleted($order);
            }
        }

        return response()->json([
            'message' => 'Order statuses updated successfully'
        ]);
    }

    /**
     * Revert seller earnings and balances for a cancelled/refunded order.
     */
    protected function revertSellerEarnings(Order $order): void
    {
        $earnings = SellerEarning::where('order_id', $order->id)
            ->whereIn('status', ['pending', 'held', 'available'])
            ->get();

        foreach ($earnings->groupBy('seller_id') as $sellerId => $sellerEarnings) {
            $netTotal = $sellerEarnings->sum('net_amount');

            DB::transaction(function () use ($sellerId, $netTotal, $sellerEarnings) {
                $balance = \App\Models\SellerBalance::where('seller_id', $sellerId)
                    ->lockForUpdate()
                    ->first();

                if (!$balance) {
                    return;
                }

                $pendingAmount   = $sellerEarnings->whereIn('status', ['pending', 'held'])->sum('net_amount');
                $availableAmount = $sellerEarnings->where('status', 'available')->sum('net_amount');

                if ($pendingAmount > 0) {
                    $balance->pending_balance = max(0, bcsub($balance->pending_balance, $pendingAmount, 2));
                }

                if ($availableAmount > 0) {
                    $balance->available_balance = max(0, bcsub($balance->available_balance, $availableAmount, 2));
                }

                $balance->total_earned = max(0, bcsub($balance->total_earned, $netTotal, 2));
                $balance->save();
            });
        }

        SellerEarning::where('order_id', $order->id)
            ->whereIn('status', ['pending', 'held', 'available'])
            ->delete();
    }

    protected function applyFilters($query, array $filters): void
    {
        $grouped = collect($filters)->groupBy('name');

        $fields    = $grouped['field[]'] ?? [];
        $operators = $grouped['operator[]'] ?? [];
        $values    = $grouped['value[]'] ?? [];

        $allowedOperators = ['=', '!=', '<', '>', '<=', '>=', 'like', 'LIKE'];

        foreach ($fields as $i => $fieldItem) {

            $field    = $fieldItem['value'] ?? null;
            $operator = $operators[$i]['value'] ?? '=';
            $value    = $values[$i]['value'] ?? null;

            if (!$field || $value === null || $value === '') {
                continue;
            }

            if (!in_array($operator, $allowedOperators, true)) {
                $operator = '=';
            }

            if (strtolower($operator) === 'like') {
                $operator = 'LIKE';
                $value = "%{$value}%";
            }

            switch ($field) {
                case 'status':
                    $query->where('orders.status', $operator, $value);
                    break;
                case 'created_at':
                    $query->whereDate('orders.created_at', $value);
                    break;
                case 'amount':
                    $query->where('orders.total_amount', $operator, $value);
                    break;
                case 'customer_name':
                    $query->whereHas('addresses', fn ($q) =>
                        $q->where('type', 'billing')->where('name', $operator, $value)
                    );
                    break;
                case 'customer_email':
                    $query->whereHas('addresses', fn ($q) =>
                        $q->where('type', 'billing')->where('email', $operator, $value)
                    );
                    break;
                case 'customer_phone':
                    $query->whereHas('addresses', fn ($q) =>
                        $q->where('type', 'billing')->where('phone', $operator, $value)
                    );
                    break;
                case 'payment_method':
                    $query->whereHas('transactions', fn ($q) =>
                        $q->where('payment_method', $operator, $value)
                    );
                    break;
                case 'payment_status':
                    $query->where('orders.payment_status', $operator, $value);
                    break;
            }
        }
    }

    public function resendNotification(Request $request, Order $order)
    {
        $request->validate([
            'type' => 'required|in:order_placed,payment_confirmed,order_completed,invoice',
        ]);

        $order->loadMissing(['user', 'items.seller', 'invoice']);

        if (!$order->user) {
            return response()->json(['message' => 'No customer associated with this order.'], 422);
        }

        try {
            switch ($request->type) {
                case 'order_placed':
                    OrderNotificationService::orderPlaced($order);
                    $label = 'Order placed';
                    break;

                case 'payment_confirmed':
                    if ($order->payment_status !== 'paid') {
                        return response()->json(['message' => 'Order is not paid yet.'], 422);
                    }
                    OrderNotificationService::paymentConfirmed($order);
                    $label = 'Payment confirmed';
                    break;

                case 'order_completed':
                    if ($order->status !== 'completed') {
                        return response()->json(['message' => 'Order is not completed yet.'], 422);
                    }
                    OrderNotificationService::orderCompleted($order);
                    $label = 'Order completed / delivery';
                    break;

                case 'invoice':
                    if (!$order->invoice) {
                        return response()->json(['message' => 'Invoice not generated yet.'], 422);
                    }
                    $order->user->notify(new \App\Notifications\OrderPaymentNotification($order, 'customer'));
                    $label = 'Invoice';
                    break;

                default:
                    return response()->json(['message' => 'Unknown notification type.'], 422);
            }

            $order->notes()->create([
                'user_id' => auth()->id(),
                'note'    => "{$label} notification resent by admin.",
                'type'    => 'system',
                'is_visible_to_customer' => false,
            ]);

            return response()->json(['message' => "{$label} notification sent successfully."]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Failed to send notification: ' . $e->getMessage()], 500);
        }
    }

    public function retryDelivery(OrderDelivery $delivery)
    {
        if ($delivery->status !== 'failed') {
            return back()->with('error', 'Only failed deliveries can be retried.');
        }

        $delivery->update(['status' => 'pending', 'payload' => null]);

        app(OrderDeliveryService::class)->autoDeliver($delivery);

        $delivery->refresh();
        $status = $delivery->status === 'delivered' ? 'success' : 'error';
        $message = $delivery->status === 'delivered'
            ? 'Delivery retried successfully.'
            : 'Delivery retry failed. Check order notes for details.';

        return back()->with($status, $message);
    }
}
