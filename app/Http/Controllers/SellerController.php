<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\SellerEarning;
use App\Models\Setting;
use App\Models\User;
use App\Services\SellerBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            $query = Seller::with('user');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('verified')) {
                $query->where('is_verified', $request->verified === 'yes');
            }

            return $this->sellerDataTable($query);
        }

        $stats = [
            'total'     => Seller::count(),
            'active'    => Seller::where('status', 'active')->count(),
            'pending'   => Seller::where('status', 'pending')->count(),
            'suspended' => Seller::where('status', 'suspended')->count(),
            'verified'  => Seller::where('is_verified', true)->count(),
        ];

        return view('content.sellers.index', compact('stats'));
    }

    /**
     * Show seller details with all stats, earnings, withdrawals, offers.
     */
    public function show(Request $request, Seller $seller)
    {
        $seller->load(['user', 'balance']);

        $balance = app(SellerBalanceService::class)->getOrCreateBalance($seller->id);

        // Earnings DataTable
        if ($request->ajax() && $request->get('table') === 'earnings') {
            return DataTables::of(
                SellerEarning::where('seller_id', $seller->id)
                    ->with(['order:id,order_number,total_amount,status', 'orderItem:id,product_id', 'orderItem.product:id,title'])
                    ->latest()
            )
            ->addColumn('order_number', fn($row) => $row->order?->order_number ?? '-')
            ->addColumn('product', fn($row) => e($row->orderItem?->product?->title ?? '-'))
            ->addColumn('gross', fn($row) => format_currency($row->gross_amount))
            ->addColumn('commission_amount', fn($row) => format_currency($row->commission))
            ->addColumn('net', fn($row) => format_currency($row->net_amount))
            ->addColumn('status_badge', fn($row) => '<span class="badge bg-' . match($row->status) {
                'available' => 'success', 'pending' => 'warning', 'paid' => 'info', default => 'secondary'
            } . '">' . ucfirst($row->status) . '</span>')
            ->addColumn('date', fn($row) => $row->created_at->format('d M Y'))
            ->rawColumns(['status_badge'])
            ->make(true);
        }

        // Withdrawals DataTable
        if ($request->ajax() && $request->get('table') === 'withdrawals') {
            return DataTables::of(
                $seller->withdrawals()->latest()
            )
            ->addColumn('amount_fmt', fn($row) => '<strong>' . format_currency($row->amount) . '</strong>')
            ->addColumn('method_fmt', fn($row) => ucfirst($row->method))
            ->addColumn('status_badge', fn($row) => '<span class="badge bg-' . match($row->status) {
                'approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger', default => 'secondary'
            } . '">' . ucfirst($row->status) . '</span>')
            ->addColumn('date', fn($row) => $row->created_at->format('d M Y'))
            ->rawColumns(['amount_fmt', 'status_badge'])
            ->make(true);
        }

        // Offers DataTable
        if ($request->ajax() && $request->get('table') === 'offers') {
            return DataTables::of(
                $seller->offers()->with('product:id,title,image')->latest()
            )
            ->addColumn('product_col', function ($row) {
                $img = $row->product?->image ? asset($row->product->image) : asset('assets/img/avatars/1.png');
                return '<div class="d-flex align-items-center"><img src="' . $img . '" class="rounded me-2" width="36" height="36"><span>' . e($row->product?->title ?? '-') . '</span></div>';
            })
            ->addColumn('price', fn($row) => format_currency($row->retail_price))
            ->addColumn('status_badge', fn($row) => '<span class="badge bg-' . match($row->status) {
                'active' => 'success', 'inactive' => 'secondary', 'suspended' => 'danger', default => 'secondary'
            } . '">' . ucfirst($row->status) . '</span>')
            ->addColumn('keys_count', fn($row) => $row->keys()->where('status', 'available')->count())
            ->addColumn('date', fn($row) => $row->created_at->format('d M Y'))
            ->rawColumns(['product_col', 'status_badge'])
            ->make(true);
        }

        // Aggregated stats
        $stats = [
            'total_orders'    => SellerEarning::where('seller_id', $seller->id)->distinct('order_id')->count('order_id'),
            'active_offers'   => $seller->offers()->where('status', 'active')->count(),
            'total_offers'    => $seller->offers()->count(),
            'pending_earnings' => $seller->earnings()->where('status', 'pending')->count(),
            'available_earnings' => $seller->earnings()->where('status', 'available')->count(),
        ];

        return view('content.sellers.show', compact('seller', 'balance', 'stats'));
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
            'user_id'             => 'required|exists:users,id|unique:sellers,user_id',
            'store_name'          => 'required|string|max:255',
            'slug'                => 'nullable|string|max:255|unique:sellers,slug',
            'description'         => 'nullable|string',
            'email'               => 'nullable|email|max:255',
            'phone'               => 'nullable|string|max:50',
            'website'             => 'nullable|url|max:255',
            'company_name'        => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'vat_number'          => 'nullable|string|max:100',
            'tax_id'              => 'nullable|string|max:100',
            'address'             => 'nullable|string|max:500',
            'city'                => 'nullable|string|max:100',
            'state'               => 'nullable|string|max:100',
            'country'             => 'nullable|string|max:100',
            'postal_code'         => 'nullable|string|max:20',
            'status'              => 'required|in:pending,active,suspended,rejected',
            'is_verified'         => 'sometimes|boolean',
            'logo'                => 'nullable|image|max:2048',
            'banner'              => 'nullable|image|max:4096',
        ]);

        DB::transaction(function () use ($request, &$validated) {

            $validated['slug'] = !empty($validated['slug'])
                ? $validated['slug']
                : Str::slug($validated['store_name']);
            $validated['is_verified'] = $request->boolean('is_verified');

            if ($request->hasFile('logo')) {
                $validated['logo'] = $this->uploadImage($request, 'logo', 'uploads/sellers/logos');
            }

            if ($request->hasFile('banner')) {
                $validated['banner'] = $this->uploadImage($request, 'banner', 'uploads/sellers/banners');
            }

            $seller = Seller::create($validated);
            $seller->syncUserRoles();
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
            'store_name'          => 'required|string|max:255',
            'slug'                => 'nullable|string|max:255|unique:sellers,slug,' . $seller->id,
            'description'         => 'nullable|string',
            'email'               => 'nullable|email|max:255',
            'phone'               => 'nullable|string|max:50',
            'website'             => 'nullable|url|max:255',
            'company_name'        => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'vat_number'          => 'nullable|string|max:100',
            'tax_id'              => 'nullable|string|max:100',
            'address'             => 'nullable|string|max:500',
            'city'                => 'nullable|string|max:100',
            'state'               => 'nullable|string|max:100',
            'country'             => 'nullable|string|max:100',
            'postal_code'         => 'nullable|string|max:20',
            'status'              => 'required|in:pending,active,suspended,rejected',
            'is_verified'         => 'sometimes|boolean',
            'logo'                => 'nullable|image|max:2048',
            'banner'              => 'nullable|image|max:4096',
        ]);

        $oldStatus = $seller->status;

        DB::transaction(function () use ($request, $seller, &$validated, $oldStatus) {

            $validated['is_verified'] = $request->boolean('is_verified');

            if ($request->hasFile('logo')) {
                $this->deletePublicFile($seller->logo);
                $validated['logo'] = $this->uploadImage($request, 'logo', 'uploads/sellers/logos');
            }

            if ($request->hasFile('banner')) {
                $this->deletePublicFile($seller->banner);
                $validated['banner'] = $this->uploadImage($request, 'banner', 'uploads/sellers/banners');
            }

            $seller->update($validated);

            if ($oldStatus !== $validated['status']) {
                $seller->syncUserRoles();
            }
        });

        if ($oldStatus !== $validated['status']) {
            try {
                $notifMap = [
                    'active'    => ['key' => 'seller_approved',     'class' => \App\Notifications\Seller\SellerApprovedNotification::class],
                    'rejected'  => ['key' => 'seller_rejected',     'class' => \App\Notifications\Seller\SellerRejectedNotification::class],
                    'suspended' => ['key' => 'seller_suspended',    'class' => \App\Notifications\Seller\SellerSuspendedNotification::class],
                ];

                if ($oldStatus === 'suspended' && $validated['status'] === 'active') {
                    if (Setting::get('notifications', 'seller_reactivated', true)) {
                        $seller->user->notify(new \App\Notifications\Seller\SellerReactivatedNotification($seller));
                    }
                } elseif (isset($notifMap[$validated['status']])) {
                    $entry = $notifMap[$validated['status']];
                    if (Setting::get('notifications', $entry['key'], true)) {
                        $seller->user->notify(new ($entry['class'])($seller));
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Seller notification failed: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('sellers.edit', $seller->id)
            ->with('success', 'Seller updated successfully.');
    }

    /**
     * Delete seller.
     */
    public function destroy(Seller $seller)
    {

        try {
            DB::transaction(function () use ($seller) {
                $this->deletePublicFile($seller->logo);
                $this->deletePublicFile($seller->banner);
                $seller->delete();
            });

            return response()->json(['message' => 'Seller deleted successfully']);
        } catch (\Throwable $e) {
            Log::error('Seller delete failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Delete failed'], 500);
        }
    }

    /**
     * Bulk status change.
     */
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'exists:sellers,id',
            'status' => 'required|in:active,suspended,rejected',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $sellers = Seller::whereIn('id', $request->ids)->get();

                foreach ($sellers as $seller) {
                    $oldStatus = $seller->status;
                    $seller->update(['status' => $request->status]);
                    $seller->syncUserRoles();

                    try {
                        if ($oldStatus === 'suspended' && $request->status === 'active') {
                            if (Setting::get('notifications', 'seller_reactivated', true)) {
                                $seller->user->notify(new \App\Notifications\Seller\SellerReactivatedNotification($seller));
                            }
                        } elseif ($request->status === 'active' && Setting::get('notifications', 'seller_approved', true)) {
                            $seller->user->notify(new \App\Notifications\Seller\SellerApprovedNotification($seller));
                        } elseif ($request->status === 'suspended' && Setting::get('notifications', 'seller_suspended', true)) {
                            $seller->user->notify(new \App\Notifications\Seller\SellerSuspendedNotification($seller));
                        } elseif ($request->status === 'rejected' && Setting::get('notifications', 'seller_rejected', true)) {
                            $seller->user->notify(new \App\Notifications\Seller\SellerRejectedNotification($seller));
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Bulk seller notification failed: ' . $e->getMessage());
                    }
                }
            });

            return response()->json(['message' => 'Status updated for selected sellers']);
        } catch (\Throwable $e) {
            Log::error('Bulk status change failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Bulk status change failed'], 500);
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

        $stats = [
            'pending_count'   => Seller::where('status', 'pending')->count(),
            'pending_balance' => Seller::where('status', 'pending')
                ->withSum('balance', 'pending_balance')
                ->get()
                ->sum('balance_sum_pending_balance') ?? 0,
        ];

        return view('content.sellers.pending', compact('stats'));
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

        $stats = [
            'suspended_count' => Seller::where('status', 'suspended')->count(),
        ];

        return view('content.sellers.suspended', compact('stats'));
    }

    /**
     * DataTable builder.
     */
    private function sellerDataTable($query)
    {
        return DataTables::of($query->with('balance'))
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
                    <img src="'.$avatar.'" class="rounded-circle me-3" width="38" height="38" style="object-fit:cover">
                    <div class="lh-sm">
                        <a href="'.route('sellers.show', $row->id).'" class="fw-semibold text-body text-truncate">'.e($row->store_name).'</a>
                        <div class="text-muted small">'.e($row->email ?? $row->user->email).'</div>
                    </div>
                </div>';
            })

            ->addColumn('balance', function ($row) {
                $avail = $row->balance?->available_balance ?? 0;
                $pending = $row->balance?->pending_balance ?? 0;
                return '<span class="fw-semibold text-success">' . format_currency($avail) . '</span>'
                     . ($pending > 0 ? '<br><small class="text-warning">' . format_currency($pending) . ' pending</small>' : '');
            })

            ->addColumn('status_badge', fn($row) =>
                '<span class="badge bg-label-'.match($row->status){
                    'active'=>'success','pending'=>'warning','suspended'=>'danger',default=>'secondary'
                }.'">'.ucfirst($row->status).'</span>'
            )

            ->addColumn('is_verified_badge', fn($row) =>
                $row->is_verified
                    ? '<span class="badge bg-label-success"><i class="ti tabler-circle-check me-1 ti-xs"></i>Verified</span>'
                    : '<span class="badge bg-label-warning"><i class="ti tabler-clock me-1 ti-xs"></i>Unverified</span>'
            )

            ->addColumn('actions', fn($row) =>
                '<div class="d-flex align-items-center justify-content-center gap-1">
                    <a href="'.route('sellers.show', $row->id).'" class="btn btn-icon btn-sm btn-label-info" title="View">
                        <i class="ti tabler-eye ti-xs"></i>
                    </a>
                    <a href="'.route('sellers.edit', $row->id).'" class="btn btn-icon btn-sm btn-label-primary" title="Edit">
                        <i class="ti tabler-pencil ti-xs"></i>
                    </a>
                    <button type="button" class="btn btn-icon btn-sm btn-label-danger delete-btn" data-url="'.route('sellers.destroy', $row->id).'" title="Delete">
                        <i class="ti tabler-trash ti-xs"></i>
                    </button>
                </div>'
            )

            ->rawColumns(['checkbox', 'seller_column', 'balance', 'status_badge', 'is_verified_badge', 'actions'])
            ->make(true);
    }

    private function uploadImage(Request $request, string $field, string $dir): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

        $file->move(public_path($dir), $filename);

        return $dir . '/' . $filename;
    }

    private function deletePublicFile(?string $path): void
    {
        if ($path && file_exists(public_path($path))) {
            @unlink(public_path($path));
        }
    }
}
