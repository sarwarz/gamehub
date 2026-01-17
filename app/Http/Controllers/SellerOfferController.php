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
            return $this->offerDataTable(
                SellerOffer::query(), // all offers
                $currencyService
            );
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
            'status' => 'required|in:active,inactive,draft,suspended',
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
            'status' => 'required|in:active,inactive,draft,suspended',
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

    /**
     * Pending offers (DataTable)
     */
    public function pending(Request $request, CurrencyService $currencyService)
    {
        if ($request->ajax()) {
            return $this->offerDataTable(
                SellerOffer::where('status', 'inactive'),
                $currencyService
            );
        }

        return view('content.seller_offers.pending');
    }

    /**
     * Rejected offers (DataTable)
     */
    public function rejected(Request $request, CurrencyService $currencyService)
    {
        if ($request->ajax()) {
            return $this->offerDataTable(
                SellerOffer::where('status', 'suspended'),
                $currencyService
            );
        }

        return view('content.seller_offers.rejected');
    }

    private function offerDataTable($query, CurrencyService $currencyService)
    {
        $currencyCode   = $currencyService->code();
        $currencySymbol = $currencyService->symbol();

        $query->with([
            'seller:id,store_name',
            'product:id,title,cover_image'
        ]);

        return DataTables::of($query)
            ->addColumn('checkbox', fn($o) =>
                '<input type="checkbox" class="form-check-input bulk-checkbox" value="'.$o->id.'">'
            )

            ->addColumn('seller', fn($o) => e($o->seller->store_name ?? 'N/A'))

            ->addColumn('product', function ($o) {
                return '
                    <div class="d-flex align-items-center">
                        <img src="'.asset('storage/'.$o->product->cover_image).'"
                            class="rounded me-2" width="40" height="40">
                        <span>'.e($o->product->title).'</span>
                    </div>
                ';
            })

            ->editColumn('retail_price', fn($o) =>
                $currencySymbol.' '.number_format($o->retail_price, 2).' '.$currencyCode
            )

            ->addColumn('status_badge', function ($o) {
                $map = [
                    'active'   => 'success',
                    'pending'  => 'warning',
                    'suspended' => 'danger',
                    'inactive' => 'secondary',
                ];

                return '<span class="badge bg-'.$map[$o->status].'">'
                    .ucfirst($o->status).
                '</span>';
            })

            ->addColumn('actions', function ($o) {
                return '
                    <a href="'.route('seller-offers.edit', $o->id).'"
                        class="btn btn-sm btn-primary me-1">Edit</a>
                    <button data-id="'.$o->id.'"
                        class="btn btn-sm btn-danger delete-offer">Delete</button>
                ';
            })

            ->rawColumns(['checkbox', 'product', 'status_badge', 'actions'])
            ->make(true);
    }


}
