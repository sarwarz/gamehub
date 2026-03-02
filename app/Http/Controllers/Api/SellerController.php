<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Seller;
use App\Models\SellerBalance;
use App\Models\SellerEarning;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

/**
 * @group Sellers
 *
 * APIs for browsing sellers and managing your own seller store.
 */
class SellerController extends Controller
{
    /**
     * List sellers
     *
     * Browse active sellers with optional filters. Returns public seller profiles only.
     *
     * @unauthenticated
     *
     * @queryParam verified boolean Only verified sellers. Example: true
     * @queryParam country string Filter by country code. Example: US
     * @queryParam search string Search by store name. Example: Tech
     * @queryParam per_page integer Results per page (default 12). Example: 10
     *
     * @response 200 {"status":true,"message":"Sellers fetched successfully","data":{"current_page":1,"data":[],"total":0}}
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $sellers = Seller::with('user:id,name')
                ->active()
                ->when($request->verified, fn ($q) => $q->where('is_verified', true))
                ->when($request->country, fn ($q) => $q->where('country', $request->country))
                ->when($request->search, fn ($q) => $q->where('store_name', 'like', '%' . $request->search . '%'))
                ->latest()
                ->paginate(min($request->integer('per_page', 12), 50));

            return $this->success($sellers, 'Sellers fetched successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Get seller details
     *
     * Retrieve a seller's public profile including store info, rating,
     * and active offers count. Sensitive financial data is hidden.
     *
     * @unauthenticated
     *
     * @urlParam id integer required Seller ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Seller details fetched","data":{"id":1,"store_name":"Tech Store","slug":"tech-store","rating":4.8,"total_sales":150,"is_verified":true,"offers_count":12}}
     * @response 404 {"status":false,"message":"Seller not found"}
     */
    public function show($id): JsonResponse
    {
        try {
            $seller = Seller::with('user:id,name')
                ->withCount(['offers' => fn ($q) => $q->where('status', 'active')])
                ->active()
                ->find($id);

            if (!$seller) {
                return $this->error('Seller not found', 404);
            }

            $data = $seller->only([
                'id', 'store_name', 'slug', 'logo', 'banner', 'description',
                'website', 'country', 'state', 'city',
                'rating', 'total_sales', 'total_products', 'is_verified', 'created_at',
            ]);
            $data['offers_count'] = $seller->offers_count;
            $data['user'] = $seller->user?->only(['id', 'name']);

            $productIds = $seller->offers()->pluck('product_id')->unique();
            $reviewQuery = ProductReview::whereIn('product_id', $productIds)->where('status', 'approved');
            $data['review_stats'] = [
                'total_reviews'  => $reviewQuery->clone()->count(),
                'average_rating' => round($reviewQuery->clone()->avg('rating') ?? 0, 2),
            ];

            return $this->success($data, 'Seller details fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Create seller store
     *
     * Register a new seller store for the authenticated user.
     * Each user can only have one seller account. New stores start with "pending" status.
     *
     * @authenticated
     *
     * @bodyParam store_name string required Store name. Example: My Software Shop
     * @bodyParam slug string required Unique URL slug. Example: my-software-shop
     * @bodyParam email string required Contact email. Example: seller@example.com
     * @bodyParam phone string optional Contact phone. Example: +1234567890
     * @bodyParam description string optional Store description. Example: We sell premium software keys.
     * @bodyParam country string required Country code. Example: US
     * @bodyParam state string optional State/Province. Example: California
     * @bodyParam city string optional City. Example: San Francisco
     * @bodyParam address string optional Street address. Example: 123 Market St
     * @bodyParam postal_code string optional Postal code. Example: 94105
     * @bodyParam company_name string optional Legal company name. Example: My Software LLC
     * @bodyParam vat_number string optional VAT number. Example: US123456789
     * @bodyParam website string optional Website URL. Example: https://mysoftwareshop.com
     *
     * @response 201 {"status":true,"message":"Seller store created successfully","data":{"id":1,"store_name":"My Software Shop","status":"pending"}}
     * @response 409 {"status":false,"message":"You already have a seller account."}
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_name'          => 'required|string|max:255',
            'slug'                => 'required|string|max:255|unique:sellers,slug',
            'email'               => 'required|email|max:255',
            'phone'               => 'nullable|string|max:30',
            'description'         => 'nullable|string|max:2000',
            'country'             => 'required|string|max:100',
            'state'               => 'nullable|string|max:100',
            'city'                => 'nullable|string|max:100',
            'address'             => 'nullable|string|max:500',
            'postal_code'         => 'nullable|string|max:20',
            'company_name'        => 'nullable|string|max:255',
            'vat_number'          => 'nullable|string|max:50',
            'website'             => 'nullable|url|max:255',
        ]);

        try {
            if (Seller::where('user_id', $request->user()->id)->exists()) {
                return $this->error('You already have a seller account.', 409);
            }

            $data['user_id']     = $request->user()->id;
            $data['status']      = 'pending';
            $data['is_verified'] = false;

            $seller = Seller::create($data);

            return $this->success($seller, 'Seller store created successfully', 201);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Update seller store
     *
     * Update your seller store information. Only the store owner can update.
     *
     * @authenticated
     *
     * @urlParam id integer required Seller ID. Example: 1
     *
     * @bodyParam store_name string optional Store name. Example: Updated Store Name
     * @bodyParam description string optional Store description.
     * @bodyParam phone string optional Phone number.
     * @bodyParam website string optional Website URL.
     * @bodyParam country string optional Country code. Example: US
     * @bodyParam state string optional State/Province.
     * @bodyParam city string optional City.
     * @bodyParam address string optional Street address.
     * @bodyParam postal_code string optional Postal code.
     * @bodyParam company_name string optional Legal company name.
     * @bodyParam vat_number string optional VAT number.
     *
     * @response 200 {"status":true,"message":"Seller updated successfully","data":{"id":1,"store_name":"Updated Store Name"}}
     * @response 403 {"status":false,"message":"Unauthorized or seller not found"}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'store_name'          => 'sometimes|string|max:255',
            'description'         => 'sometimes|string|max:2000',
            'phone'               => 'nullable|string|max:30',
            'website'             => 'nullable|url|max:255',
            'country'             => 'sometimes|string|max:100',
            'state'               => 'nullable|string|max:100',
            'city'                => 'nullable|string|max:100',
            'address'             => 'nullable|string|max:500',
            'postal_code'         => 'nullable|string|max:20',
            'company_name'        => 'nullable|string|max:255',
            'vat_number'          => 'nullable|string|max:50',
        ]);

        try {
            $seller = Seller::where('id', $id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$seller) {
                return $this->error('Unauthorized or seller not found', 403);
            }

            $seller->update($data);

            return $this->success($seller->fresh(), 'Seller updated successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Get my seller profile
     *
     * Retrieve the authenticated user's seller profile with balance information.
     * Unlike the public show endpoint, this returns full financial details.
     *
     * @authenticated
     *
     * @response 200 {"status":true,"message":"Seller profile fetched","data":{"seller":{"id":1,"store_name":"My Store","status":"active"},"balance":{"available_balance":"150.00","pending_balance":"50.00","total_earned":"500.00","total_paid":"300.00"}}}
     * @response 404 {"status":false,"message":"Seller account not found."}
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $seller = Seller::with(['balance', 'user:id,name,email'])
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$seller) {
                return $this->error('Seller account not found.', 404);
            }

            return $this->success([
                'seller'  => $seller,
                'balance' => $seller->balance ?? [
                    'available_balance' => '0.00',
                    'pending_balance'   => '0.00',
                    'total_earned'      => '0.00',
                    'total_paid'        => '0.00',
                ],
            ], 'Seller profile fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Seller dashboard
     *
     * Get seller dashboard statistics including sales overview, revenue breakdown,
     * recent orders, and balance summary. Essential for the seller's home screen.
     *
     * @authenticated
     *
     * @response 200 {"status":true,"message":"Dashboard loaded","data":{"stats":{"total_orders":45,"pending_orders":3,"completed_orders":40,"cancelled_orders":2,"total_revenue":"1250.00","total_commission":"125.00","net_earnings":"1125.00"},"balance":{"available":"500.00","pending":"200.00","total_earned":"1125.00","total_paid":"425.00"},"recent_orders":[]}}
     * @response 404 {"status":false,"message":"Seller account not found."}
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $seller = Seller::where('user_id', $request->user()->id)->first();

            if (!$seller) {
                return $this->error('Seller account not found.', 404);
            }

            $earnings = SellerEarning::where('seller_id', $seller->id);

            $stats = [
                'total_orders'     => $earnings->clone()->distinct('order_id')->count('order_id'),
                'pending_orders'   => Order::whereHas('items', fn ($q) => $q->where('seller_id', $seller->id))
                                        ->where('status', 'pending')->count(),
                'completed_orders' => Order::whereHas('items', fn ($q) => $q->where('seller_id', $seller->id))
                                        ->where('status', 'completed')->count(),
                'cancelled_orders' => Order::whereHas('items', fn ($q) => $q->where('seller_id', $seller->id))
                                        ->where('status', 'cancelled')->count(),
                'total_revenue'    => $earnings->clone()->sum('gross_amount'),
                'total_commission' => $earnings->clone()->sum('commission'),
                'net_earnings'     => $earnings->clone()->sum('net_amount'),
            ];

            $balance = SellerBalance::firstOrNew(
                ['seller_id' => $seller->id],
                ['available_balance' => 0, 'pending_balance' => 0, 'total_earned' => 0, 'total_paid' => 0]
            );

            $recentOrders = Order::whereHas('items', fn ($q) => $q->where('seller_id', $seller->id))
                ->with(['items' => fn ($q) => $q->where('seller_id', $seller->id)->with('product:id,title,slug,image')])
                ->with('user:id,name,email')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($order) => [
                    'id'             => $order->id,
                    'order_number'   => $order->order_number,
                    'customer'       => $order->user?->name,
                    'items_count'    => $order->items->count(),
                    'status'         => $order->status,
                    'payment_status' => $order->payment_status,
                    'created_at'     => $order->created_at?->toISOString(),
                ]);

            $productIds = $seller->offers()->pluck('product_id')->unique();
            $reviewQuery = ProductReview::whereIn('product_id', $productIds)->where('status', 'approved');

            return $this->success([
                'stats'         => $stats,
                'balance'       => [
                    'available'    => $balance->available_balance,
                    'pending'      => $balance->pending_balance,
                    'total_earned' => $balance->total_earned,
                    'total_paid'   => $balance->total_paid,
                ],
                'recent_orders' => $recentOrders,
                'review_stats'  => [
                    'total_reviews'  => $reviewQuery->clone()->count(),
                    'average_rating' => round($reviewQuery->clone()->avg('rating') ?? 0, 2),
                    'seller_rating'  => $seller->rating,
                ],
            ], 'Dashboard loaded');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Seller balance
     *
     * Get detailed balance breakdown for the authenticated seller.
     *
     * @authenticated
     *
     * @response 200 {"status":true,"message":"Balance fetched","data":{"available_balance":"500.00","pending_balance":"200.00","total_earned":"1125.00","total_paid":"425.00"}}
     * @response 404 {"status":false,"message":"Seller account not found."}
     */
    public function balance(Request $request): JsonResponse
    {
        try {
            $seller = Seller::where('user_id', $request->user()->id)->first();

            if (!$seller) {
                return $this->error('Seller account not found.', 404);
            }

            $balance = SellerBalance::firstOrNew(
                ['seller_id' => $seller->id],
                ['available_balance' => 0, 'pending_balance' => 0, 'total_earned' => 0, 'total_paid' => 0]
            );

            return $this->success($balance, 'Balance fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }
}
