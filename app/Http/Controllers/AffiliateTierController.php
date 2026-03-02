<?php

namespace App\Http\Controllers;

use App\Models\AffiliateTier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AffiliateTierController extends Controller
{
    public function index()
    {
        $tiers = AffiliateTier::ordered()->get();
        return view('content.affiliates.tiers', compact('tiers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                    => 'required|string|max:50',
            'commission_rate'         => 'required|numeric|min:0|max:100',
            'l2_commission_rate'      => 'nullable|numeric|min:0|max:100',
            'min_earnings_threshold'  => 'nullable|numeric|min:0',
            'min_referrals'           => 'nullable|integer|min:0',
            'min_conversions'         => 'nullable|integer|min:0',
            'color'                   => 'nullable|string|max:30',
            'sort_order'              => 'nullable|integer',
            'is_default'              => 'sometimes|boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['l2_commission_rate']     = $data['l2_commission_rate'] ?? 0;
        $data['min_earnings_threshold'] = $data['min_earnings_threshold'] ?? 0;
        $data['min_referrals']          = $data['min_referrals'] ?? 0;
        $data['min_conversions']        = $data['min_conversions'] ?? 0;

        if (!empty($data['is_default'])) {
            AffiliateTier::where('is_default', true)->update(['is_default' => false]);
        }

        AffiliateTier::create($data);

        return response()->json(['message' => 'Tier created successfully.']);
    }

    public function update(Request $request, AffiliateTier $affiliateTier)
    {
        $data = $request->validate([
            'name'                    => 'required|string|max:50',
            'commission_rate'         => 'required|numeric|min:0|max:100',
            'l2_commission_rate'      => 'nullable|numeric|min:0|max:100',
            'min_earnings_threshold'  => 'nullable|numeric|min:0',
            'min_referrals'           => 'nullable|integer|min:0',
            'min_conversions'         => 'nullable|integer|min:0',
            'color'                   => 'nullable|string|max:30',
            'sort_order'              => 'nullable|integer',
            'is_default'              => 'sometimes|boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);

        if (!empty($data['is_default'])) {
            AffiliateTier::where('is_default', true)->where('id', '!=', $affiliateTier->id)->update(['is_default' => false]);
        }

        $affiliateTier->update($data);

        return response()->json(['message' => 'Tier updated successfully.']);
    }

    public function destroy(AffiliateTier $affiliateTier)
    {
        if ($affiliateTier->affiliates()->exists()) {
            return response()->json(['message' => 'Cannot delete tier with active affiliates.'], 422);
        }

        $affiliateTier->delete();
        return response()->json(['message' => 'Tier deleted.']);
    }
}
