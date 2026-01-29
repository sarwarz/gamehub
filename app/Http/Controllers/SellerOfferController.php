<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\Product;
use App\Models\SellerOffer;
use Illuminate\Http\Request;
use App\Models\SellerOfferKey;
use Yajra\DataTables\DataTables;

class SellerOfferController extends Controller
{
    /**
     * Display all offers (DataTable).
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = SellerOffer::query()
                ->with([
                    'seller:id,store_name,email,logo',
                    'product:id,title,image',
                ]);

            /*
            |--------------------------------------------------------------------------
            | APPLY DYNAMIC FILTERS (Orders-style)
            |--------------------------------------------------------------------------
            */
            foreach ($request->filters ?? [] as $filter) {

                if (
                    empty($filter['field']) ||
                    !array_key_exists('value', $filter) ||
                    $filter['value'] === '' ||
                    $filter['value'] === null
                ) {
                    continue;
                }

                $field    = $filter['field'];
                $operator = $filter['operator'] ?? '=';
                $value    = $filter['value'];

                switch ($field) {

                    /* ===============================
                    | DIRECT FIELDS
                    =============================== */
                    case 'status':
                    case 'sale_mode':
                    case 'retail_price':
                    case 'wholesale_10_99_price':
                    case 'wholesale_100_plus_price':
                        $this->applyOperator($query, $field, $operator, $value);
                        break;

                    case 'is_verified':
                    case 'is_promoted':
                        $query->where($field, (bool) $value);
                        break;

                    case 'created_at':
                        $query->whereDate('created_at', $value);
                        break;

                    /* ===============================
                    | RELATIONS
                    =============================== */
                    case 'seller_id':
                    case 'product_id':
                        $query->where($field, $value);
                        break;
                }
            }


            return $this->offerDataTable($query);
        }

        /*
        |--------------------------------------------------------------------------
        | NORMAL PAGE LOAD
        |--------------------------------------------------------------------------
        */
        return view('content.seller_offers.index', [
            'sellers'  => Seller::select('id', 'store_name')->orderBy('store_name')->get(),
            'products' => Product::select('id', 'title')->orderBy('title')->get(),
        ]);
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
        SellerOffer::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'status' => 'required|in:active,inactive'
        ]);

        SellerOffer::whereIn('id', $request->ids)
            ->update(['status' => $request->status]);

        return response()->json(['success' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array'
        ]);

        SellerOffer::whereIn('id', $request->ids)->delete();

        return response()->json(['success' => true]);
    }


    


    public function toggleStatus(Request $request, SellerOffer $offer)
    {
        $request->validate([
            'status' => 'required|in:active,inactive'
        ]);

        $offer->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Offer status updated successfully.',
            'status'  => $offer->status
        ]);
    }

    private function offerDataTable($query, )
    {

        $query->with([
            'seller:id,store_name,email,logo',
            'product:id,title,image'
        ]);

        return DataTables::of($query)

            ->addColumn('checkbox', fn($o) =>
                '<input type="checkbox" class="form-check-input bulk-checkbox" value="'.$o->id.'">'
            )

            ->addColumn('seller', function ($o) {

                $seller = $o->seller;

                $avatar = $seller->logo
                    ? asset($seller->logo)
                    : 'https://ui-avatars.com/api/?name='.urlencode($seller->store_name).'&background=0D8ABC&color=fff';

                return '
                    <div class="d-flex align-items-center">
                        <img src="'.$avatar.'"
                            class="rounded-circle me-2"
                            width="36" height="36"
                            alt="Avatar">

                        <div class="lh-sm">
                            <div class="fw-semibold">'.e($seller->store_name).'</div>
                            <small class="text-muted">'.e($seller->email).'</small>
                        </div>
                    </div>
                ';
            })


            ->filterColumn('seller', function ($query, $keyword) {
                $query->whereHas('seller', function ($q) use ($keyword) {
                    $q->where('store_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
                });
            })


            ->addColumn('product', function ($row) {

                $image = $row->product?->image
                    ? asset($row->product->image)
                    : asset('assets/img/default-product.png');


                return '
                    <div class="d-flex align-items-center">
                        <img src="'.$image.'"
                            class="rounded me-2" width="40" height="40">
                        <span>'.e($row->product->title).'</span>
                    </div>
                ';
            })

            ->filterColumn('product', function ($query, $keyword) {
                $query->whereHas('product', function ($q) use ($keyword) {
                    $q->where('title', 'like', "%{$keyword}%");
                });
            })

            ->editColumn('retail_price', fn($o) =>
                format_currency($o->retail_price)
            )

            ->editColumn('wholesale_10_99_price', fn($o) =>
                format_currency($o->wholesale_10_99_price)
            )

            ->editColumn('wholesale_100_plus_price', fn($o) =>
                format_currency($o->wholesale_100_plus_price)
            )


            ->addColumn('status_badge', function ($o) {
                $map = [
                    'active'    => 'success',
                    'inactive'  => 'secondary',
                    'suspended' => 'danger',
                ];

                return '<span class="badge bg-'.$map[$o->status].'">'
                    .ucfirst($o->status).
                '</span>';
            })

            ->addColumn('actions', function ($offer) {


                return view('partials.action-dropdown', [
                    'editUrl'          => route('seller-offers.edit', $offer),
                    'deleteId'         => $offer->id,

                    'showStatusToggle' => true,
                    'isActive'         => $offer->status === 'active',
                    'toggleId'         => $offer->id,
                ])->render();


            })

            ->rawColumns(['checkbox', 'seller', 'product', 'status_badge', 'actions'])
            ->make(true);
    }




    private function applyOperator($query, $field, $operator, $value)
    {
        return match ($operator) {

            '='  => $query->where($field, '=', $value),
            '!=' => $query->where($field, '!=', $value),

            '>'  => $query->where($field, '>', $value),
            '<'  => $query->where($field, '<', $value),
            '>=' => $query->where($field, '>=', $value),
            '<=' => $query->where($field, '<=', $value),

            'like' =>
                $query->where($field, 'LIKE', "%{$value}%"),

            'not_like' =>
                $query->where($field, 'NOT LIKE', "%{$value}%"),

            'in' =>
                $query->whereIn($field, array_map('trim', explode(',', $value))),

            'not_in' =>
                $query->whereNotIn($field, array_map('trim', explode(',', $value))),

            default => $query
        };
    }





}
