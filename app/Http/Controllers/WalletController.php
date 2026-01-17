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
                    <a href="'.route('wallets.transactions', $w).'"
                    class="btn btn-sm btn-primary">
                        Transactions
                    </a>
                ')

                ->rawColumns(['user','status','actions'])
                ->make(true);
        }

        return view('content.wallets.index');
    }

    public function transactions(Request $request, Wallet $wallet)
    {
        if ($request->ajax()) {
            $transactions = $wallet->transactions()->latest();

            return DataTables::of($transactions)
                ->addColumn('type', fn ($t) =>
                    $t->type === 'credit'
                        ? '<span class="badge bg-success">Credit</span>'
                        : '<span class="badge bg-danger">Debit</span>'
                )

                ->addColumn('amount', fn ($t) =>
                    number_format($t->amount, 2)
                )

                ->rawColumns(['type'])
                ->make(true);
        }

        return view('content.wallets.transactions', compact('wallet'));
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

            $transactions = WalletTransaction::with([
                'wallet.user'
            ])->latest();

            return DataTables::of($transactions)

                ->addColumn('user', function ($t) {
                    return '
                        <strong>'.$t->wallet->user->name.'</strong><br>
                        <small class="text-muted">'.$t->wallet->user->email.'</small>
                    ';
                })

                ->addColumn('type', function ($t) {
                    return $t->type === 'credit'
                        ? '<span class="badge bg-success">Credit</span>'
                        : '<span class="badge bg-danger">Debit</span>';
                })

                ->addColumn('amount', function ($t) {
                    return number_format($t->amount, 2);
                })

                ->addColumn('source', function ($t) {
                    return $t->source ?? '—';
                })

                ->addColumn('reference', function ($t) {
                    return $t->reference_type
                        ? $t->reference_type.' #'.$t->reference_id
                        : '—';
                })

                ->addColumn('date', function ($t) {
                    return $t->created_at->format('d M Y, h:i A');
                })

                ->rawColumns(['user', 'type'])
                ->make(true);
        }

        return view('content.wallets.all-transactions');
    }





}
