<?php

namespace App\Http\Controllers;

use App\Models\SellerOffer;
use Illuminate\Http\Request;
use App\Models\SellerOfferKey;
use App\Services\CurrencyService;
use Yajra\DataTables\DataTables;

class SellerOfferController extends Controller
{
    /**
     * Display all offers (DataTable).
     */
    public function index(Request $request, CurrencyService $currencyService)
    {
        if ($request->ajax()) {
            $currencyCode = $currencyService->code(); // e.g. "USD"
            $currencySymbol = $currencyService->symbol(); // e.g. "$"

            $offers = SellerOffer::with([
                'seller:id,store_name',
                'product' => [
                    'regions:id,name',
                    'languages:id,name',
                    'platforms:id,name',
                ]
            ]);

            return DataTables::of($offers)
                ->addColumn('checkbox', function ($offer) {
                    return '<input type="checkbox" class="form-check-input bulk-checkbox" value="'.$offer->id.'">';
                })
                ->addColumn('seller_column', fn($offer) => e($offer->seller->store_name ?? 'N/A'))
                ->addColumn('product_column', function ($offer) {
                    return '<div class="d-flex align-items-center">
                                <img src="'.asset('storage/'.$offer->product->cover_image).'" alt="cover"
                                    class="rounded me-2" width="40" height="40">
                                <span>'.e($offer->product->title).'</span>
                            </div>';
                })
                ->editColumn('retail_price', function ($offer) use ($currencyCode, $currencySymbol) {
                    return $currencySymbol . ' ' . number_format($offer->retail_price, 2) . ' ' . $currencyCode;
                })
                ->editColumn('wholesale_10_99_price', function ($offer) use ($currencyCode, $currencySymbol) {
                    return $currencySymbol . ' ' . number_format($offer->wholesale_10_99_price, 2) . ' ' . $currencyCode;
                })
                ->editColumn('wholesale_100_plus_price', function ($offer) use ($currencyCode, $currencySymbol) {
                    return $currencySymbol . ' ' . number_format($offer->wholesale_100_plus_price, 2) . ' ' . $currencyCode;
                })
                ->addColumn('status_badge', function ($offer) {
                    $class = match($offer->status) {
                        'active'   => 'badge bg-success',
                        'inactive' => 'badge bg-danger',
                        default    => 'badge bg-secondary',
                    };
                    return '<span class="'.$class.'">'.ucfirst($offer->status).'</span>';
                })
                ->addColumn('actions', function ($offer) {
                    return '<a href="'.route('seller-offers.edit', $offer->id).'" 
                                class="btn btn-sm btn-primary me-2">Edit</a>
                            <button data-id="'.$offer->id.'" 
                                class="btn btn-sm btn-danger delete-offer">Delete</button>';
                })
                ->rawColumns(['checkbox', 'product_column', 'status_badge', 'actions'])
                ->make(true);
        }

        return view('content.seller_offers.index');
    }


    /**
     * Show form to create a new offer.
     */
    public function create()
    {
        // You can preload products & sellers for dropdowns
        return view('content.seller_offers.create');
    }

    /**
     * Store new offer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'seller_id'   => 'required|exists:sellers,id',
            'product_id'  => 'required|exists:products,id',
            'retail_price' => 'required|numeric|min:0',
            'retail_acquisition_cost' => 'required|numeric|min:0',
            'wholesale_10_99_price' => 'nullable|numeric|min:0',
            'wholesale_10_99_acquisition_cost' => 'nullable|numeric|min:0',
            'wholesale_100_plus_price' => 'nullable|numeric|min:0',
            'wholesale_100_acquisition_cost' => 'nullable|numeric|min:0',
            'sale_mode' => 'required|in:retail,wholesale,both',
            'is_verified' => 'boolean',
            'is_promoted' => 'boolean',
            'status' => 'required|in:active,inactive,draft',
            'keys_text' => 'required|string',
        ]);

        $validated['is_verified'] = $request->has('is_verified');
        $validated['is_promoted'] = $request->has('is_promoted');

        // Check for duplicate offer
        $duplicate = SellerOffer::where('seller_id', $validated['seller_id'])
            ->where('product_id', $validated['product_id'])
            ->exists();

        if ($duplicate) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'This seller already has an offer for the selected product.');
        }

        $offer = SellerOffer::create($validated);

         // Save keys into seller_offer_keys table
        if ($request->filled('keys_text')) {
            $keysArray = preg_split('/\r\n|[\r\n]/', trim($request->keys_text));
            foreach ($keysArray as $key) {
                if (!empty($key)) {
                    SellerOfferKey::create([
                        'seller_offer_id' => $offer->id,
                        'type'   => 'text',
                        'value'  => $key,
                        'status' => 'available',
                    ]);
                }
            }
        }

        return redirect()->route('seller-offers.index')
                         ->with('success', 'Offer created successfully.');
    }

    /**
     * Edit offer.
     */
    public function edit($id)
    {
        $offer = SellerOffer::with('product')->findOrFail($id);
        return view('content.seller_offers.edit', compact('offer'));
    }

    /**
     * Update offer.
     */
    public function update(Request $request, $id)
    {
        $offer = SellerOffer::findOrFail($id);

        $validated = $request->validate([
            'seller_id'   => 'required|exists:sellers,id',
            'product_id'  => 'required|exists:products,id',
            'retail_price' => 'required|numeric|min:0',
            'retail_acquisition_cost' => 'nullable|numeric|min:0',
            'wholesale_10_99_price' => 'nullable|numeric|min:0',
            'wholesale_10_99_acquisition_cost' => 'nullable|numeric|min:0',
            'wholesale_100_plus_price' => 'nullable|numeric|min:0',
            'wholesale_100_acquisition_cost' => 'nullable|numeric|min:0',
            'sale_mode' => 'required|in:retail,wholesale,both',
            'is_verified' => 'boolean',
            'is_promoted' => 'boolean',
            'status' => 'required|in:active,inactive,draft',
            'keys_text' => 'required|string',
        ]);

        $validated['is_verified'] = $request->has('is_verified');
        $validated['is_promoted'] = $request->has('is_promoted');

        $offer->update($validated);

        // Optional: Reset keys and re-insert
        if ($request->filled('keys_text')) {
            $offer->keys()->delete(); // ⚠ careful, deletes all old keys
            $keysArray = preg_split('/\r\n|[\r\n]/', trim($request->keys_text));
            foreach ($keysArray as $key) {
                if (!empty($key)) {
                    SellerOfferKey::create([
                        'seller_offer_id' => $offer->id,
                        'type'   => 'text',
                        'value'  => $key,
                        'status' => 'available',
                    ]);
                }
            }
        }

        return redirect()->route('seller-offers.index')
                        ->with('success', 'Offer updated successfully.');
    }


    /**
     * Delete offer.
     */
    public function destroy($id)
    {
        $offer = SellerOffer::findOrFail($id);
        $offer->delete();

        return response()->json(['success' => true]);
    }
}
