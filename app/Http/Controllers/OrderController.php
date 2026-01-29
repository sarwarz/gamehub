<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
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
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;
use App\Services\OrderDeliveryService;
use App\Services\InvoiceService;

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

                    return format_currency($order->total_amount);
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
        $paymentMethods = PaymentMethod::all();
        $products = Product::active()->get();

        return view('content.orders.create', compact('users','paymentMethods','products'));
    }

    /**
     * Store a new order with items + payment.
     */
    /**
     * Store manually created admin order
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
            'status'                       => ['required', 'string'],
        ]);

        DB::transaction(function () use ($request) {

            $buyer = User::findOrFail($request->buyer_id);

            $subtotal = 0;
            $orderItems = [];

            /**
             * STEP 1: Validate offers & calculate totals
             */
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

            /**
             * STEP 2: Create Order
             */
            $order = Order::create([
                'user_id'          => $buyer->id,
                'currency'         => 'USD',

                'subtotal'         => $subtotal,
                'tax_amount'       => $taxAmount,
                'discount_amount'  => $discountAmount,
                'total_amount'     => $totalAmount,

                'payment_method'   => $request->payment_method,
                'payment_reference'=> $request->payment_ref,
                'payment_status'   => 'paid',

                'status'           => $request->status,

                'meta' => [
                    'created_by' => [
                        'type' => 'admin',
                        'id'   => auth()->id(),
                    ],
                    'flags' => [
                        'manual_order' => true,
                        'auto_delivery'=> true,
                    ],
                ],
            ]);

            /**
             * STEP 3: Billing Address
             */
            OrderAddress::create([
                'order_id' => $order->id,
                'type'     => 'billing',
                ...$request->billing,
            ]);

            /**
             * STEP 4: Items, Deliveries & Seller Earnings
             */
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

                $commission = round($data['lineTotal'] * ($offer->commission / 100), 2);
                $netAmount  = $data['lineTotal'] - $commission;

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

            /**
             * STEP 5: Transaction Record
             */
            Transaction::create([
                'user_id'         => $buyer->id,
                'reference_type'  => Order::class,
                'reference_id'    => $order->id,
                'trx'             => uniqid('trx_'),
                'amount'          => $order->total_amount,
                'fee'             => 0,
                'net_amount'      => $order->total_amount,
                'currency'        => $order->currency,
                'type'            => 'debit',
                'category'        => 'order',
                'status'          => 'completed',
                'payment_method' => $request->payment_method,
            ]);
        });

        return redirect()
            ->route('orders.index')
            ->with('success', 'Order created successfully.');
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
            'items.product:id,title,image',
            'items.seller:id,store_name',
            'items.deliveries',
            'items.earning',
            'addresses',
            'transactions' => fn ($q) => $q->latest(),
        ])->findOrFail($id);

        // ✅ Extract addresses
        $billingAddress  = $order->addresses->firstWhere('type', 'billing');
        $shippingAddress = $order->addresses->firstWhere('type', 'shipping');

        return view('content.orders.edit', compact(
            'order',
            'billingAddress',
            'shippingAddress'
        ));
    }





    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,refunded,cancelled',
        ]);

        if (in_array($order->status, ['cancelled'])) {
            return back()->with('warning', 'Order can no longer be updated.');
        }

        if (
            in_array($request->status, ['completed','refunded']) &&
            $order->payment_status !== 'paid'
        ) {
            return back()->with('warning', 'Order must be paid first.');
        }

        $order->update(['status' => $request->status]);

        /**
         * 🔁 RE-TRIGGER DELIVERY IF NOT COMPLETED
         */
        if ($request->status === 'completed') {

            $order->load('items.deliveries');

            foreach ($order->items as $item) {
                foreach ($item->deliveries as $delivery) {

                    // Only retry pending or failed deliveries
                    if (in_array($delivery->status, ['pending','failed'])) {

                        // Auto delivery
                        if ($delivery->delivery_method === 'auto') {
                            app(OrderDeliveryService::class)
                                ->autoDeliver($delivery);
                        }

                        // Manual delivery → do nothing (admin must deliver manually)
                    }
                }
            }

            // Generate invoice if missing
            if (
                $request->status === 'completed' &&
                $order->payment_status === 'paid' &&
                !$order->invoice
            ) {
                app(InvoiceService::class)->generateFromOrder($order);
            }
            

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
