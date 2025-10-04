<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the currencies (DataTable).
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $currencies = Currency::query();

            return DataTables::of($currencies)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="bulk-checkbox form-check-input" value="'.$row->id.'">';
                })
                ->addColumn('default_badge', function ($row) {
                    return $row->is_default
                        ? '<span class="badge bg-success">Yes</span>'
                        : '<span class="badge bg-secondary">No</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('actions', function ($row) {
                    return '
                        <button class="btn btn-sm btn-warning me-2"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasCurrencyForm"
                            data-edit="true"
                            data-id="'.$row->id.'"
                            data-code="'.$row->code.'"
                            data-name="'.$row->name.'"
                            data-symbol="'.$row->symbol.'"
                            data-is_default="'.$row->is_default.'"
                            data-is_active="'.$row->is_active.'"
                            data-url="'.route('currencies.update', $row->id).'">
                            Edit
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete" data-url="'.route('currencies.destroy', $row->id).'">Delete</button>
                    ';
                })
                ->rawColumns(['checkbox', 'default_badge', 'status_badge', 'actions'])
                ->make(true);
        }

        return view('content.currencies.index');
    }

    /**
     * Store a newly created currency.
     */
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
            // Ensure only one default
            if (!empty($validated['is_default']) && $validated['is_default']) {
                Currency::where('is_default', true)->update(['is_default' => false]);
            }

            Currency::create($validated);

            return redirect()->route('currencies.index')->with('success', 'Currency created successfully.');
        } catch (\Exception $e) {
            Log::error('Currency create failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create currency. Please try again.');
        }
    }

    /**
     * Update the specified currency.
     */
    public function update(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        $validated = $request->validate([
            'code'       => 'required|string|max:10|unique:currencies,code,'.$currency->id,
            'name'       => 'required|string|max:255',
            'symbol'     => 'nullable|string|max:10',
            'is_default' => 'boolean',
            'is_active'  => 'boolean',
        ]);

        try {
            // Ensure only one default
            if (!empty($validated['is_default']) && $validated['is_default']) {
                Currency::where('is_default', true)->where('id', '!=', $currency->id)->update(['is_default' => false]);
            }

            $currency->update($validated);

            return redirect()->route('currencies.index')->with('success', 'Currency updated successfully.');
        } catch (\Exception $e) {
            Log::error('Currency update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update currency. Please try again.');
        }
    }

    /**
     * Remove the specified currency.
     */
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

    /**
     * Bulk delete currencies.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'No currencies selected'], 400);
        }

        try {
            $currencies = Currency::whereIn('id', $ids)->get();

            foreach ($currencies as $currency) {
                if ($currency->is_default) {
                    continue; // skip deleting default currency
                }
                $currency->delete();
            }

            return response()->json(['message' => 'Selected currencies deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Bulk currency delete failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete currencies'], 500);
        }
    }


    public function updateRates()
    {
        try {
            $apiKey = config('services.currencyapi.key'); // store in config/services.php
            $response = Http::get("https://api.currencyapi.com/v3/latest", [
                'apikey' => $apiKey,
                'base_currency' => 'USD'
            ]);

            if ($response->failed()) {
                return response()->json(['status' => 'error', 'message' => 'Failed to fetch rates'], 500);
            }

            $data = $response->json();

            if (!isset($data['data'])) {
                return response()->json(['status' => 'error', 'message' => 'Invalid API response'], 500);
            }

            foreach ($data['data'] as $code => $info) {
                Currency::where('code', $code)->update([
                    'rate'       => $info['value'],
                    'fetched_at' => now(),
                ]);
            }

            // Keep default currency = 1.0
            $defaultCurrency = Currency::where('is_default', true)->first();
            if ($defaultCurrency) {
                $defaultCurrency->update(['rate' => 1.0]);
            }

            return response()->json(['status' => 'success', 'message' => 'Rates updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


}
