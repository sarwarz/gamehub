<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SellerController extends Controller
{
    /**
     * Display a listing of the sellers.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $sellers = Seller::with(['user', 'paymentInfo']);

            return DataTables::of($sellers)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="bulk-checkbox form-check-input" value="' . $row->id . '">';
                })
                ->addColumn('seller_column', function ($row) {
                    $avatar = $row->logo
                        ? asset('storage/'.$row->logo)
                        : asset('assets/img/avatars/1.png');

                    $name = e($row->user->name ?? $row->store_name);
                    $email = e($row->email ?? $row->user->email ?? 'N/A');

                    return '
                        <div class="d-flex justify-content-start align-items-center">
                            <div class="avatar-wrapper">
                                <div class="avatar avatar me-2 me-sm-4 rounded-2 bg-label-secondary">
                                    <img src="'.$avatar.'" alt="'.$name.'" class="rounded" width="40" height="40">
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <h6 class="mb-0">'.$name.'</h6>
                                <small class="text-truncate d-none d-sm-block">'.$email.'</small>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('store_name', fn($row) => e($row->store_name))
                ->addColumn('status_badge', function ($row) {
                    $class = match($row->status) {
                        'active'    => 'success',
                        'pending'   => 'warning',
                        'suspended' => 'danger',
                        default     => 'secondary',
                    };
                    return '<span class="badge bg-'.$class.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('is_verified_badge', function ($row) {
                    return $row->is_verified
                        ? '<span class="badge bg-success">Verified</span>'
                        : '<span class="badge bg-warning">Unverified</span>';
                })
                ->addColumn('total_sales', fn($row) => number_format($row->total_sales))
                ->addColumn('balance', function ($row) {
                    $balance = $row->paymentInfo->current_balance ?? 0;
                    return '<span class="text-success">$'.number_format($balance, 2).'</span>';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl   = route('sellers.edit', $row->id);
                    $deleteUrl = route('sellers.destroy', $row->id);

                    return '
                        <div class="btn-group">
                            <a href="'.$editUrl.'" class="btn btn-sm btn-primary" title="Edit">
                                <i class="ti ti-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-danger btn-delete" 
                                data-url="'.$deleteUrl.'" title="Delete">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns([
                    'checkbox',
                    'seller_column',
                    'status_badge',
                    'is_verified_badge',
                    'balance',
                    'actions'
                ])
                ->make(true);
        }

        return view('content.sellers.index');
    }


    /**
     * Show the form for creating a new seller.
     */
    public function create()
    {
        // fetch all users (or filter if needed)
        $users = \App\Models\User::all();

        return view('content.sellers.create', compact('users'));
    }

    /**
     * Store a newly created seller.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'store_name'  => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:sellers,slug',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'status'      => 'required|in:pending,active,suspended',
            'is_verified' => 'boolean',
        ]);

        $seller = Seller::create($validated);

        
        $seller->paymentInfo()->create([
            'preferred_method' => 'bank',
            'minimum_payout'   => 50.00,
            'current_balance'  => 0,
            'payout_balance'   => 0,
        ]);


        return redirect()->route('sellers.index')->with('success', 'Seller created successfully.');
    }

    /**
     * Show the form for editing the specified seller.
     */
    public function edit($id)
    {
        $seller = Seller::findOrFail($id);
        $users = \App\Models\User::all();
        return view('content.sellers.edit', compact('seller','users'));
    }

    /**
     * Update the specified seller in storage.
     */
    public function update(Request $request, $id)
    {
        $seller = Seller::findOrFail($id);

        $validated = $request->validate([
            'store_name'  => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:sellers,slug,' . $seller->id,
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'status'      => 'required|in:pending,active,suspended',
            'is_verified' => 'boolean',
        ]);

        $seller->update($validated);

        return redirect()->route('sellers.index')->with('success', 'Seller updated successfully.');
    }

    /**
     * Remove the specified seller.
     */
    public function destroy($id)
    {
        try {
            $seller = Seller::findOrFail($id);
            $seller->delete();

            return response()->json(['message' => 'Seller deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Seller delete failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete seller.'], 500);
        }
    }

    /**
     * Bulk delete sellers.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'No sellers selected'], 400);
        }

        try {
            DB::transaction(function () use ($ids) {
                Seller::whereIn('id', $ids)->delete();
            });

            return response()->json(['message' => 'Selected sellers deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Bulk seller delete failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete sellers'], 500);
        }
    }
}
