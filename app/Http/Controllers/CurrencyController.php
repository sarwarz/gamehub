<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;

class CurrencyController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $currencies = Currency::query();

            return DataTables::of($currencies)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="bulk-checkbox form-check-input" value="' . $row->id . '">';
                })
                ->addColumn('code_col', function ($row) {
                    $flag = '<span class="fw-bold text-primary" style="font-size:1.05rem;letter-spacing:1px">' . e($row->code) . '</span>';
                    $name = '<small class="text-muted d-block">' . e($row->name) . '</small>';
                    return $flag . $name;
                })
                ->addColumn('symbol_col', function ($row) {
                    if ($row->symbol) {
                        return '<span class="badge bg-label-dark fs-6 px-3">' . e($row->symbol) . '</span>';
                    }
                    return '<span class="text-muted">—</span>';
                })
                ->addColumn('rate_col', function ($row) {
                    $val = $row->rate == 1 ? '1.00' : number_format($row->rate, 6);
                    $base = $row->rate == 1 ? '<small class="badge bg-label-info ms-1">Base</small>' : '';
                    $fetched = $row->fetched_at
                        ? '<small class="text-muted d-block"><i class="ti tabler-clock" style="font-size:.75rem"></i> ' . \Carbon\Carbon::parse($row->fetched_at)->diffForHumans() . '</small>'
                        : '';
                    return '<span class="fw-semibold">' . $val . '</span>' . $base . $fetched;
                })
                ->addColumn('default_badge', function ($row) {
                    return $row->is_default
                        ? '<span class="badge bg-label-success"><i class="ti tabler-star-filled me-1" style="font-size:.7rem"></i>Default</span>'
                        : '<span class="badge bg-label-secondary">No</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->is_active
                        ? '<span class="badge bg-label-success">Active</span>'
                        : '<span class="badge bg-label-danger">Inactive</span>';
                })
                ->addColumn('actions', function ($row) {
                    $edit = '<button type="button" class="btn btn-icon btn-sm btn-label-primary edit-btn"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasCurrencyForm"
                        data-edit="true"
                        data-id="' . $row->id . '"
                        data-code="' . e($row->code) . '"
                        data-name="' . e($row->name) . '"
                        data-symbol="' . e($row->symbol) . '"
                        data-is_default="' . $row->is_default . '"
                        data-is_active="' . $row->is_active . '"
                        data-url="' . route('currencies.update', $row->id) . '"
                        title="Edit">
                        <i class="ti tabler-pencil ti-xs"></i>
                    </button>';

                    $setDefault = '';
                    if (!$row->is_default) {
                        $setDefault = '<button type="button" class="btn btn-icon btn-sm btn-label-warning set-default-btn" data-id="' . $row->id . '" data-url="' . route('currencies.update', $row->id) . '" data-name="' . e($row->name) . '" title="Set Default">
                            <i class="ti tabler-star ti-xs"></i>
                        </button>';
                    }

                    $toggle = $row->is_active
                        ? '<button type="button" class="btn btn-icon btn-sm btn-label-info toggle-status-btn" data-id="' . $row->id . '" data-url="' . route('currencies.update', $row->id) . '" data-status="0" title="Deactivate">
                            <i class="ti tabler-toggle-right ti-xs"></i>
                        </button>'
                        : '<button type="button" class="btn btn-icon btn-sm btn-label-info toggle-status-btn" data-id="' . $row->id . '" data-url="' . route('currencies.update', $row->id) . '" data-status="1" title="Activate">
                            <i class="ti tabler-toggle-left ti-xs"></i>
                        </button>';

                    $delete = '';
                    if (!$row->is_default) {
                        $delete = '<button type="button" class="btn btn-icon btn-sm btn-label-danger delete-btn" data-id="' . $row->id . '" data-url="' . route('currencies.destroy', $row->id) . '" title="Delete">
                            <i class="ti tabler-trash ti-xs"></i>
                        </button>';
                    }

                    return '<div class="d-flex align-items-center justify-content-center gap-1">'
                        . $edit . $setDefault . $toggle . $delete
                        . '</div>';
                })
                ->rawColumns(['checkbox', 'code_col', 'symbol_col', 'rate_col', 'default_badge', 'status_badge', 'actions'])
                ->make(true);
        }

        return view('content.currencies.index');
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'code'       => 'required|string|max:10|unique:currencies,code',
            'name'       => 'required|string|max:255',
            'symbol'     => 'nullable|string|max:10',
            'is_default' => 'boolean',
            'is_active'  => 'boolean',
        ]);

        try {
            if (!empty($validated['is_default']) && $validated['is_default']) {
                Currency::where('is_default', true)->update(['is_default' => false]);
            }

            Currency::create($validated);

            return redirect()->route('currencies.index')->with('success', 'Currency created successfully.');
        } catch (\Exception $e) {
            Log::error('Currency create failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create currency.');
        }
    }

    public function update(Request $request, $id)
    {

        $currency = Currency::findOrFail($id);

        // Quick AJAX toggle (status or default) — partial update
        if ($request->ajax() && $request->has('_quick')) {
            try {
                $data = [];

                if ($request->has('is_active')) {
                    $data['is_active'] = (bool) $request->input('is_active');
                }

                if ($request->has('is_default') && $request->input('is_default')) {
                    Currency::where('is_default', true)->where('id', '!=', $currency->id)->update(['is_default' => false]);
                    $data['is_default'] = true;
                }

                $currency->update($data);

                return response()->json(['message' => 'Currency updated successfully.']);
            } catch (\Exception $e) {
                Log::error('Currency quick update failed: ' . $e->getMessage());
                return response()->json(['message' => 'Failed to update currency.'], 500);
            }
        }

        $validated = $request->validate([
            'code'       => 'required|string|max:10|unique:currencies,code,' . $currency->id,
            'name'       => 'required|string|max:255',
            'symbol'     => 'nullable|string|max:10',
            'is_default' => 'boolean',
            'is_active'  => 'boolean',
        ]);

        try {
            if (!empty($validated['is_default']) && $validated['is_default']) {
                Currency::where('is_default', true)->where('id', '!=', $currency->id)->update(['is_default' => false]);
            }

            $currency->update($validated);

            if ($request->ajax()) {
                return response()->json(['message' => 'Currency updated successfully.']);
            }

            return redirect()->route('currencies.index')->with('success', 'Currency updated successfully.');
        } catch (\Exception $e) {
            Log::error('Currency update failed: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['message' => 'Failed to update currency.'], 500);
            }
            return redirect()->back()->with('error', 'Failed to update currency.');
        }
    }

    public function destroy($id)
    {

        try {
            $currency = Currency::findOrFail($id);

            if ($currency->is_default) {
                return response()->json(['message' => 'Default currency cannot be deleted.'], 400);
            }

            $currency->delete();
            return response()->json(['message' => 'Currency deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Currency delete failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete currency.'], 500);
        }
    }

    public function bulkDelete(Request $request)
    {

        $ids = $request->input('ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'No currencies selected.'], 400);
        }

        try {
            $defaultIds = Currency::where('is_default', true)->pluck('id')->toArray();
            $toDelete = array_diff($ids, $defaultIds);

            if (empty($toDelete)) {
                return response()->json(['message' => 'Cannot delete default currency.'], 400);
            }

            $count = Currency::whereIn('id', $toDelete)->delete();

            return response()->json(['message' => $count . ' currencies deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Bulk currency delete failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete currencies.'], 500);
        }
    }

    public function updateRates()
    {
        try {
            $defaultCurrency = Currency::where('is_default', true)->first();

            if (!$defaultCurrency) {
                return response()->json(['status' => 'error', 'message' => 'No default currency set. Please set a default currency first.'], 400);
            }

            $apiKey = config('services.currencyapi.key');

            if (!$apiKey) {
                return response()->json(['status' => 'error', 'message' => 'Currency API key not configured. Add CURRENCY_API_KEY to your .env file.'], 500);
            }

            $response = Http::timeout(15)->get('https://api.currencyapi.com/v3/latest', [
                'apikey'        => $apiKey,
                'base_currency' => $defaultCurrency->code,
            ]);

            if ($response->failed()) {
                return response()->json(['status' => 'error', 'message' => 'Failed to fetch exchange rates from API.'], 500);
            }

            $updated = 0;
            foreach ($response->json('data') as $code => $info) {
                $affected = Currency::where('code', $code)->update([
                    'rate'       => $info['value'],
                    'fetched_at' => now(),
                ]);
                $updated += $affected;
            }

            $defaultCurrency->update(['rate' => 1.0, 'fetched_at' => now()]);

            return response()->json([
                'status'  => 'success',
                'message' => "Exchange rates updated for {$updated} currencies (base: {$defaultCurrency->code})."
            ]);
        } catch (\Exception $e) {
            Log::error('Exchange rate update failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong while updating rates.'], 500);
        }
    }
}
