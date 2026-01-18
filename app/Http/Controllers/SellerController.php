<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class SellerController extends Controller
{
    /**
     * Display sellers list.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->sellerDataTable(
                Seller::with('user')
            );
        }

        return view('content.sellers.index');
    }

    /**
     * Create seller form.
     */
    public function create()
    {
        $users = User::whereDoesntHave('roles', function ($q) {
            $q->where('name', 'seller');
        })->get();

        return view('content.sellers.create', compact('users'));
    }

    /**
     * Store new seller.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'     => 'required|exists:users,id|unique:sellers,user_id',
            'store_name'  => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:sellers,slug',
            'description' => 'nullable|string',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'website'     => 'nullable|url|max:255',
            'status'      => 'required|in:pending,active,suspended',
            'is_verified' => 'sometimes|boolean',
            'logo'        => 'nullable|image|max:2048',
            'banner'      => 'nullable|image|max:4096',
        ]);

        DB::transaction(function () use ($request, &$validated) {

            $validated['slug'] ??= Str::slug($validated['store_name']);
            $validated['is_verified'] = $request->boolean('is_verified');

            if ($request->hasFile('logo')) {
                $validated['logo'] = $request->file('logo')
                    ->store('sellers/logos', 'public');
            }

            if ($request->hasFile('banner')) {
                $validated['banner'] = $request->file('banner')
                    ->store('sellers/banners', 'public');
            }

            Seller::create($validated);
        });

        return redirect()
            ->route('sellers.index')
            ->with('success', 'Seller created successfully.');
    }

    /**
     * Edit seller form.
     */
    public function edit(Seller $seller)
    {
        $users = User::all();

        return view('content.sellers.edit', compact('seller', 'users'));
    }

    /**
     * Update seller.
     */
    public function update(Request $request, Seller $seller)
    {
        $validated = $request->validate([
            'store_name'  => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:sellers,slug,' . $seller->id,
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'website'     => 'nullable|url|max:255',
            'status'      => 'required|in:pending,active,suspended',
            'is_verified' => 'sometimes|boolean',
            'logo'        => 'nullable|image|max:2048',
            'banner'      => 'nullable|image|max:4096',
        ]);

        DB::transaction(function () use ($request, $seller, &$validated) {

            $validated['is_verified'] = $request->boolean('is_verified');

            if ($request->hasFile('logo')) {

                if ($path = $this->uploadImage($request, 'logo', 'uploads/sellers/logos')) {
                    $validated['logo'] = $path;
                }
            }

            if ($request->hasFile('banner')) {
                if ($path = $this->uploadImage($request, 'banner', 'uploads/sellers/banners')) {
                    $validated['banner'] = $path;
                }
            }

            $seller->update($validated);
        });

        return redirect()
            ->route('sellers.index')
            ->with('success', 'Seller updated successfully.');
    }

    /**
     * Delete seller.
     */
    public function destroy(Seller $seller)
    {
        try {
            DB::transaction(function () use ($seller) {
                if ($seller->logo) {
                    Storage::disk('public')->delete($seller->logo);
                }
                if ($seller->banner) {
                    Storage::disk('public')->delete($seller->banner);
                }
                $seller->delete();
            });

            return response()->json(['message' => 'Seller deleted successfully']);
        } catch (\Throwable $e) {
            Log::error('Seller delete failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Delete failed'], 500);
        }
    }

    /**
     * Bulk delete.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['message' => 'No sellers selected'], 422);
        }

        try {
            Seller::whereIn('id', $ids)->delete();
            return response()->json(['message' => 'Selected sellers deleted']);
        } catch (\Throwable $e) {
            Log::error('Bulk delete failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Bulk delete failed'], 500);
        }
    }

    /**
     * Pending sellers.
     */
    public function pending(Request $request)
    {
        if ($request->ajax()) {
            return $this->sellerDataTable(
                Seller::pending()->with('user')
            );
        }

        return view('content.sellers.pending');
    }

    /**
     * Suspended sellers.
     */
    public function suspended(Request $request)
    {
        if ($request->ajax()) {
            return $this->sellerDataTable(
                Seller::where('status', 'suspended')->with('user')
            );
        }

        return view('content.sellers.suspended');
    }

    /**
     * DataTable builder.
     */
    private function sellerDataTable($query)
    {
        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('checkbox', fn($row) =>
                '<input type="checkbox" class="bulk-checkbox form-check-input" value="'.$row->id.'">'
            )

            ->addColumn('seller_column', function ($row) {
                $avatar = $row->logo
                    ? asset($row->logo)
                    : asset('assets/img/avatars/1.png');

                return '
                <div class="d-flex align-items-center">
                    <img src="'.$avatar.'" class="rounded me-2" width="40">
                    <div>
                        <strong>'.e($row->store_name).'</strong><br>
                        <small>'.e($row->email ?? $row->user->email).'</small>
                    </div>
                </div>';
            })

            ->addColumn('status_badge', fn($row) =>
                '<span class="badge bg-'.match($row->status){
                    'active'=>'success','pending'=>'warning','suspended'=>'danger',default=>'secondary'
                }.'">'.ucfirst($row->status).'</span>'
            )

            ->addColumn('is_verified_badge', fn($row) =>
                $row->is_verified
                    ? '<span class="badge bg-success">Verified</span>'
                    : '<span class="badge bg-warning">Unverified</span>'
            )

            ->addColumn('actions', fn($row) => '
                <a href="'.route('sellers.edit',$row->id).'" class="btn btn-sm btn-primary">Edit</a>
                <button class="btn btn-sm btn-danger btn-delete" data-url="'.route('sellers.destroy',$row->id).'">Delete</button>
            ')

            ->rawColumns(['checkbox','seller_column','status_badge','is_verified_badge','actions'])
            ->make(true);
    }

    private function uploadImage(Request $request, string $field, string $path)
    {
        if ($request->hasFile($field)) {
            $file = $request->file($field);
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

            $file->move(public_path($path), $filename);

            return $path . '/' . $filename;
        }

        return null;
    }


}
