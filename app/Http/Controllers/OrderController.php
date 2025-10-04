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

class OrderController extends Controller
{
    /**
     * Display orders in DataTable or load index view.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $orders = Order::with(['buyer:id,name,email', 'latestPayment']);

            return DataTables::of($orders)
                ->addIndexColumn()
                ->addColumn('checkbox', fn($row) =>
                    '<input type="checkbox" class="bulk-checkbox form-check-input" value="'.$row->id.'">'
                )
                ->addColumn('buyer', fn($row) =>
                    $row->buyer ? e($row->buyer->name).' ('.$row->buyer->email.')' : '-'
                )
                ->addColumn('order_date', fn($row) =>
                    $row->created_at ? $row->created_at->format('M d, Y h:i A') : '-'
                )
                ->addColumn('status_badge', fn($row) => 
                    '<span class="badge bg-'.match($row->status) {
                        'pending'    => 'warning',
                        'processing' => 'info',
                        'delivered'  => 'primary',
                        'completed'  => 'success',
                        'refunded'   => 'secondary',
                        'cancelled'  => 'danger',
                        default      => 'light'
                    }.'">'.ucfirst($row->status).'</span>'
                )
                ->addColumn('payment_status', function ($row) {
                    if (!$row->latestPayment) return '<span class="badge bg-light">Unpaid</span>';
                    return '<span class="badge bg-'.match($row->latestPayment->status) {
                        'pending'    => 'warning',
                        'processing' => 'info',
                        'paid'       => 'success',
                        'failed'     => 'danger',
                        'refunded'   => 'secondary',
                        default      => 'light'
                    }.'">'.ucfirst($row->latestPayment->status).'</span>';
                })
                ->addColumn('actions', function ($row) {
                    $showUrl   = route('orders.show', $row->id);
                    $editUrl   = route('orders.edit', $row->id);
                    $deleteUrl = route('orders.destroy', $row->id);

                    return '
                        <a href="'.$showUrl.'" class="btn btn-sm btn-primary">View</a>
                        <a href="'.$editUrl.'" class="btn btn-sm btn-warning">Edit</a>
                        <button class="btn btn-sm btn-danger btn-delete" data-url="'.$deleteUrl.'">Delete</button>
                    ';
                })
                ->rawColumns(['checkbox','status_badge','payment_status','actions'])
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
    public function show($id)
    {
        $order = Order::with([
            'buyer:id,name,email',
            'items.offer.product',
            'items.offer.seller',
            'items.offer.keys',
            'payments'
        ])->findOrFail($id);

        return view('content.orders.show', compact('order'));
    }

    public function edit($id)
    {
        $order = Order::with(['buyer','items.offer.product','latestPayment'])->findOrFail($id);
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
        $ids = $request->input('ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'No orders selected'], 400);
        }

        Order::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Selected orders deleted successfully.']);
    }
}
