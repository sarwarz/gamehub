<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SellerOffer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
    /**
     * Display orders in DataTable or load index view.
     */

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
                    'payment_method',
                    'payment_status',
                    'currency',
                    'created_at'
                ]);



            // Apply filters
            if ($request->filled('filters')) {
                $this->applyFilters($orders, $request->filters);
            }


            return DataTables::of($orders)
                ->addIndexColumn()

                /* -----------------------------
                | Bulk Checkbox
                -----------------------------*/
                ->addColumn('checkbox', function ($order) {
                    return sprintf(
                        '<input type="checkbox" class="form-check-input bulk-checkbox" value="%d">',
                        $order->id
                    );
                })

                /* -----------------------------
                | Buyer Info
                -----------------------------*/
                ->addColumn('buyer', function ($order) {
                    if (!$order->user) {
                        return '-';
                    }

                    return e($order->user->name) .
                        '<br><small class="text-muted">' .
                        e($order->user->email) .
                        '</small>';
                })

                ->addColumn('payment_method', function ($order) {

                    $payment = $order->transactions->first();

                    if (!$payment || !$payment->payment_method) {
                        return '<span class="text-muted">—</span>';
                    }

                    return 'Payment with '.ucfirst($payment->payment_method);
                })


                /* -----------------------------
                | Order Date
                -----------------------------*/
                ->addColumn('order_date', function ($order) {
                    return $order->created_at
                        ? $order->created_at->format('M d, Y h:i A')
                        : '-';
                })

                ->addColumn('total_formatted', function ($order) {

                    $symbols = [
                        'USD' => '$',
                        'EUR' => '€',
                        'GBP' => '£',
                        'BDT' => '৳',
                        'INR' => '₹',
                    ];

                    $currency = strtoupper($order->currency ?? 'USD');
                    $symbol   = $symbols[$currency] ?? $currency . ' ';

                    return $symbol . number_format($order->total_amount, 2);
                })


                /* -----------------------------
                | Order Status Badge
                -----------------------------*/
                ->addColumn('status_badge', function ($order) {
                    $map = [
                        'pending'    => 'warning',
                        'processing' => 'info',
                        'completed'  => 'success',
                        'refunded'   => 'secondary',
                        'cancelled'  => 'danger',
                    ];

                    $color = $map[$order->status] ?? 'light';

                    return sprintf(
                        '<span class="badge bg-%s">%s</span>',
                        $color,
                        ucfirst($order->status)
                    );
                })

                /* -----------------------------
                | Payment Status Badge
                -----------------------------*/
                ->addColumn('payment_status', function ($order) {

                    $map = [
                        'pending'  => 'warning',
                        'paid'     => 'success',
                        'failed'   => 'danger',
                        'refunded' => 'secondary',
                    ];

                    $status = $order->payment_status ?? 'pending';
                    $color  = $map[$status] ?? 'light';

                    return sprintf(
                        '<span class="badge bg-%s">%s</span>',
                        $color,
                        ucfirst($status)
                    );
                })


                /* -----------------------------
                | Actions
                -----------------------------*/
                ->addColumn('actions', function ($order) {

                    return view('content.orders.partials.actions', [
                        'order' => $order
                    ])->render();
                })

                ->rawColumns([
                    'checkbox',
                    'buyer',
                    'status_badge',
                    'payment_status',
                    'payment_method',
                    'actions'
                ])
                ->make(true);
        }

        return view('content.orders.index');
    }


    /**
     * Show form to create new order.
     */
    public function create()
    {
        $users = User::all();
        $products = Product::active()->get();

        return view('content.orders.create', compact('users','products'));
    }

    /**
     * Store a new order with items + payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'buyer_id'        => 'required|exists:users,id',
            'status'          => 'required|in:pending,processing,delivered,completed,refunded,cancelled',
            'items'           => 'required|array|min:1',
            'items.*.product_id'      => 'required|exists:products,id',
            'items.*.seller_offer_id' => 'required|exists:seller_offers,id',
            'items.*.unit_price'      => 'required|numeric|min:0',
            'items.*.quantity'        => 'required|integer|min:1',
            'address.full_name'       => 'required|string|max:191',
            'address.address_line1'   => 'required|string|max:191',
            'address.city'            => 'required|string|max:191',
            'address.country'         => 'required|string|max:191',
            'payment_method'  => 'nullable|string|max:100',
            'transaction_id'  => 'nullable|string|max:191',
            'payment_status'  => 'nullable|in:pending,processing,paid,failed,refunded',
            'fees'            => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $subtotal = 0;

            // Create order
            $order = Order::create([
                'user_id'        => $validated['buyer_id'],
                'status'         => $validated['status'],
                'total_amount'   => 0,
                'commission_fee' => 0,
                'seller_earning' => 0,
            ]);

            // Save items
            foreach ($validated['items'] as $item) {
                $lineSubtotal = $item['unit_price'] * $item['quantity'];
                $subtotal += $lineSubtotal;

                $orderItem = OrderItem::create([
                    'order_id'        => $order->id,
                    'seller_id'       => \App\Models\SellerOffer::find($item['seller_offer_id'])->seller_id,
                    'product_id'      => $item['product_id'],
                    'seller_offer_id' => $item['seller_offer_id'],
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $item['unit_price'],
                    'subtotal'        => $lineSubtotal,
                ]);
            }

            // Commission (from product type %)
            $commission = 0;
            foreach ($order->items as $oi) {
                $productType = $oi->product->types()->first();
                if ($productType && $productType->commission > 0) {
                    $commission += ($oi->subtotal * ($productType->commission / 100));
                }
            }

            $order->update([
                'total_amount'   => $subtotal + ($validated['fees'] ?? 0),
                'commission_fee' => $commission,
                'seller_earning' => $subtotal - $commission,
            ]);

            // Save address
            if (isset($validated['address'])) {
                $order->addresses()->create(array_merge(
                    $validated['address'],
                    ['type' => 'billing']
                ));
            }

            // Save payment
            if (!empty($validated['payment_status'])) {
                Payment::create([
                    'order_id'       => $order->id,
                    'user_id'        => $order->user_id,
                    'payment_method' => $validated['payment_method'] ?? 'manual',
                    'transaction_id' => $validated['transaction_id'] ?? null,
                    'status'         => $validated['payment_status'],
                    'amount'         => $order->total_amount,
                    'currency'       => 'USD',
                    'paid_at'        => $validated['payment_status'] === 'paid' ? now() : null,
                ]);
            }
        });

        return redirect()->route('orders.index')->with('success','Order created successfully.');
    }


    /**
     * Show a single order.
     */
    public function show(int $id)
    {
        $order = Order::with([
            // Customer
            'user:id,name,email',

            // Order items and related data
            'items' => function ($q) {
                $q->with([
                    'product:id,title,slug',
                    'seller:id,store_name',
                    'offer:id,retail_price',
                    'deliveries',
                    'earning'
                ]);
            },

            // Addresses
            'addresses',

            // Transactions / Payments
            'transactions' => function ($q) {
                $q->latest();
            },
        ])->findOrFail($id);

        return view('content.orders.show', compact('order'));
    }


    public function edit(int $id)
    {
        $order = Order::with([
            'user:id,name,email',
            'items.product:id,title,cover_image',
            'items.seller:id,store_name',
            'items.deliveries',
            'items.earning',
            'addresses',
            'transactions' => fn ($q) => $q->latest(),
        ])->findOrFail($id);

        return view('content.orders.edit', compact('order'));
    }



    /**
     * Update an order (status + payment).
     */
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status'          => 'nullable|in:pending,processing,delivered,completed,refunded,cancelled',
            'payment_method'  => 'nullable|string|max:100',
            'transaction_id'  => 'nullable|string|max:191',
            'payment_status'  => 'nullable|in:pending,processing,paid,failed,refunded',
            'amount'          => 'nullable|numeric|min:0',
            'currency'        => 'nullable|string|max:10',
        ]);

        if (isset($validated['status'])) {
            $order->status = $validated['status'];
            $order->save();
        }

        if (isset($validated['payment_status'])) {
            Payment::updateOrCreate(
                [
                    'order_id'       => $order->id,
                    'transaction_id' => $validated['transaction_id'] ?? null,
                ],
                [
                    'user_id'        => $order->user_id,
                    'payment_method' => $validated['payment_method'] ?? 'manual',
                    'status'         => $validated['payment_status'],
                    'amount'         => $validated['amount'] ?? $order->total,
                    'currency'       => $validated['currency'] ?? 'USD',
                    'paid_at'        => $validated['payment_status'] === 'paid' ? now() : null,
                ]
            );
        }

        return redirect()->route('orders.index')->with('success', 'Order updated successfully.');
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array'
        ]);

        Order::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'Orders deleted successfully'
        ]);
    }


    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        Order::whereIn('id', $request->ids)
            ->update([
                'status' => $request->status
            ]);

        return response()->json([
            'message' => 'Order statuses updated successfully'
        ]);
    }


    

    protected function applyFilters($query, array $filters): void
    {
        $grouped = collect($filters)->groupBy('name');

        $fields    = $grouped['field[]'] ?? [];
        $operators = $grouped['operator[]'] ?? [];
        $values    = $grouped['value[]'] ?? [];

        foreach ($fields as $i => $fieldItem) {

            $field    = $fieldItem['value'] ?? null;
            $operator = $operators[$i]['value'] ?? '=';
            $value    = $values[$i]['value'] ?? null;

            if (!$field || $value === null || $value === '') {
                continue;
            }

            if ($operator === 'like') {
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
                        $q->where('type', 'billing')
                        ->where('name', $operator, $value)
                    );
                    break;

                case 'customer_email':
                    $query->whereHas('addresses', fn ($q) =>
                        $q->where('type', 'billing')
                        ->where('email', $operator, $value)
                    );
                    break;

                case 'customer_phone':
                    $query->whereHas('addresses', fn ($q) =>
                        $q->where('type', 'billing')
                        ->where('phone', $operator, $value)
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





    
}
