<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $wallets = Wallet::with('user');

            return DataTables::of($wallets)
                ->addColumn('user', fn ($w) =>
                    '<strong>'.$w->user->name.'</strong><br>
                    <small>'.$w->user->email.'</small>'
                )

                ->addColumn('balance', fn ($w) =>
                    number_format($w->balance, 2)
                )

                ->addColumn('status', fn ($w) =>
                    $w->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Disabled</span>'
                )

                ->addColumn('actions', fn ($w) => '
                    <div class="d-flex align-items-center justify-content-center gap-1">
                        <a href="'.route('wallets.transactions', $w).'" class="btn btn-icon btn-sm btn-label-info" title="Transactions">
                            <i class="ti tabler-eye ti-xs"></i>
                        </a>
                    </div>
                ')

                ->rawColumns(['user','status','actions'])
                ->make(true);
        }

        $stats = [
            'total'   => Wallet::count(),
            'active'  => Wallet::where('is_active', true)->count(),
            'balance' => Wallet::sum('balance'),
        ];

        return view('content.wallets.index', compact('stats'));
    }

    public function transactions(Request $request, Wallet $wallet)
    {
        if ($request->ajax()) {
            $transactions = $wallet->transactions()
                ->when($request->input('type_filter'), fn ($q, $f) => $q->where('type', $f))
                ->latest();

            return DataTables::of($transactions)
                ->addColumn('type', function ($t) {
                    if ($t->type === 'credit') {
                        return '<span class="badge bg-label-success"><i class="ti tabler-arrow-down-left ti-xs me-1"></i>Credit</span>';
                    }
                    return '<span class="badge bg-label-danger"><i class="ti tabler-arrow-up-right ti-xs me-1"></i>Debit</span>';
                })

                ->addColumn('amount', function ($t) {
                    $color = $t->type === 'credit' ? 'text-success' : 'text-danger';
                    $sign = $t->type === 'credit' ? '+' : '-';
                    return '<span class="fw-semibold '.$color.'">'.$sign.' $'.number_format($t->amount, 2).'</span>';
                })

                ->addColumn('source', function ($t) {
                    $icons = [
                        'deposit'         => 'tabler-wallet',
                        'order'           => 'tabler-shopping-cart',
                        'refund'          => 'tabler-receipt-refund',
                        'transfer'        => 'tabler-arrows-exchange',
                        'seller_transfer' => 'tabler-building-store',
                        'withdraw'        => 'tabler-cash',
                        'admin'           => 'tabler-shield',
                    ];
                    $icon = $icons[$t->source] ?? 'tabler-coin';
                    return '<span class="d-inline-flex align-items-center gap-1"><i class="ti '.$icon.' ti-xs text-muted"></i>'.ucfirst($t->source).'</span>';
                })

                ->addColumn('status', function ($t) {
                    $map = [
                        'completed' => 'bg-label-success',
                        'pending'   => 'bg-label-warning',
                        'failed'    => 'bg-label-danger',
                    ];
                    $cls = $map[$t->status] ?? 'bg-label-secondary';
                    return '<span class="badge '.$cls.'">'.ucfirst($t->status).'</span>';
                })

                ->addColumn('balance_after', fn ($t) =>
                    '<span class="fw-semibold">$'.number_format($t->balance_after, 2).'</span>'
                )

                ->editColumn('created_at', fn ($t) =>
                    '<span title="'.$t->created_at->format('Y-m-d H:i:s').'">'.$t->created_at->diffForHumans().'</span>'
                )

                ->rawColumns(['type', 'amount', 'source', 'status', 'balance_after', 'created_at'])
                ->make(true);
        }

        $wallet->load('user');

        $stats = [
            'total_credit'  => $wallet->transactions()->where('type', 'credit')->where('status', 'completed')->sum('amount'),
            'total_debit'   => $wallet->transactions()->where('type', 'debit')->where('status', 'completed')->sum('amount'),
            'total_txns'    => $wallet->transactions()->count(),
            'last_activity' => $wallet->transactions()->latest()->first()?->created_at,
        ];

        return view('content.wallets.transactions', compact('wallet', 'stats'));
    }

    public function history(Request $request)
    {
        $wallet = auth()->user()->wallet;

        if ($request->ajax()) {
            return DataTables::of($wallet->transactions()->latest())
                ->addColumn('type', fn ($t) =>
                    $t->type === 'credit' ? 'Credit' : 'Debit'
                )
                ->make(true);
        }

        return view('wallet.history');
    }

    /**
     * Credit a user's wallet
     */
    public function credit(Request $request, User $user)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $user) {

            // Lock wallet row to prevent race conditions
            $wallet = Wallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrCreate([
                    'user_id' => $user->id,
                ]);

            // Increase balance
            $wallet->balance += $request->amount;
            $wallet->save();

            // Create transaction log
            WalletTransaction::create([
                'wallet_id'   => $wallet->id,
                'amount'      => $request->amount,
                'type'        => 'credit',
                'source'      => 'admin',
                'description' => $request->description ?? 'Wallet credited by admin',
            ]);
        });

        return response()->json([
            'message' => 'Wallet credited successfully',
        ]);
    }

    /**
     * Debit a user's wallet
     */
    public function debit(Request $request, User $user)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $user) {

            // Lock wallet row
            $wallet = Wallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrCreate([
                    'user_id' => $user->id,
                ]);

            // Prevent negative balance
            if ($wallet->balance < $request->amount) {
                abort(422, 'Insufficient wallet balance');
            }

            // Decrease balance
            $wallet->balance -= $request->amount;
            $wallet->save();

            // Log transaction
            WalletTransaction::create([
                'wallet_id'   => $wallet->id,
                'amount'      => $request->amount,
                'type'        => 'debit',
                'source'      => 'admin',
                'description' => $request->description ?? 'Wallet debited by admin',
            ]);
        });

        return response()->json([
            'message' => 'Wallet debited successfully',
        ]);
    }

    public function all_transactions(Request $request)
    {
        if ($request->ajax()) {

            $transactions = WalletTransaction::with(['wallet.user'])->latest();

            if ($request->filled('type_filter')) {
                $transactions->where('type', $request->type_filter);
            }
            if ($request->filled('status_filter')) {
                $transactions->where('status', $request->status_filter);
            }
            if ($request->filled('source_filter')) {
                $transactions->where('source', $request->source_filter);
            }

            return DataTables::of($transactions)

                ->addColumn('user', function ($t) {
                    $user = $t->wallet->user ?? null;
                    if (!$user) return '<span class="text-muted">—</span>';
                    $avatar = $user->avatar_url ?? asset('assets/img/avatars/1.png');
                    return '<div class="d-flex align-items-center">'
                        . '<img src="' . $avatar . '" class="rounded-circle me-2" width="32" height="32" style="object-fit:cover">'
                        . '<div class="lh-sm">'
                        . '<span class="fw-semibold d-block">' . e($user->name) . '</span>'
                        . '<small class="text-muted">' . e($user->email) . '</small>'
                        . '</div></div>';
                })

                ->addColumn('type', function ($t) {
                    if ($t->type === 'credit') {
                        return '<span class="badge bg-label-success"><i class="ti tabler-arrow-down-left ti-xs me-1"></i>Credit</span>';
                    }
                    return '<span class="badge bg-label-danger"><i class="ti tabler-arrow-up-right ti-xs me-1"></i>Debit</span>';
                })

                ->addColumn('amount', function ($t) {
                    $color = $t->type === 'credit' ? 'text-success' : 'text-danger';
                    $sign  = $t->type === 'credit' ? '+' : '-';
                    return '<span class="fw-semibold ' . $color . '">' . $sign . ' $' . number_format($t->amount, 2) . '</span>';
                })

                ->addColumn('source', function ($t) {
                    $icons = [
                        'deposit'         => 'tabler-wallet',
                        'order'           => 'tabler-shopping-cart',
                        'refund'          => 'tabler-receipt-refund',
                        'transfer'        => 'tabler-arrows-exchange',
                        'seller_transfer' => 'tabler-building-store',
                        'withdraw'        => 'tabler-cash',
                        'admin'           => 'tabler-shield',
                    ];
                    $icon = $icons[$t->source] ?? 'tabler-coin';
                    return '<span class="d-inline-flex align-items-center gap-1"><i class="ti ' . $icon . ' ti-xs text-muted"></i>' . ucfirst(str_replace('_', ' ', $t->source ?? '—')) . '</span>';
                })

                ->addColumn('description', function ($t) {
                    $desc = $t->description ?? '—';
                    if (strlen($desc) > 40) {
                        return '<span title="' . e($desc) . '">' . e(substr($desc, 0, 40)) . '…</span>';
                    }
                    return e($desc);
                })

                ->addColumn('status', function ($t) {
                    $map = [
                        'completed' => 'bg-label-success',
                        'pending'   => 'bg-label-warning',
                        'failed'    => 'bg-label-danger',
                    ];
                    $cls = $map[$t->status] ?? 'bg-label-secondary';
                    return '<span class="badge ' . $cls . '">' . ucfirst($t->status) . '</span>';
                })

                ->addColumn('balance_after', function ($t) {
                    return '<span class="fw-semibold">$' . number_format($t->balance_after, 2) . '</span>';
                })

                ->editColumn('created_at', function ($t) {
                    return '<span title="' . $t->created_at->format('Y-m-d H:i:s') . '">'
                        . $t->created_at->diffForHumans()
                        . '</span>';
                })

                ->rawColumns(['user', 'type', 'amount', 'source', 'description', 'status', 'balance_after', 'created_at'])
                ->make(true);
        }

        $stats = [
            'total'        => WalletTransaction::count(),
            'total_credit' => WalletTransaction::where('type', 'credit')->where('status', 'completed')->sum('amount'),
            'total_debit'  => WalletTransaction::where('type', 'debit')->where('status', 'completed')->sum('amount'),
            'pending'      => WalletTransaction::where('status', 'pending')->count(),
            'completed'    => WalletTransaction::where('status', 'completed')->count(),
            'failed'       => WalletTransaction::where('status', 'failed')->count(),
        ];

        return view('content.wallets.all-transactions', compact('stats'));
    }





}
