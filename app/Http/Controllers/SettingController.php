<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = Setting::all()
            ->groupBy('group')
            ->map(fn ($items) => $items->pluck('value', 'key'))
            ->toArray();

        $currencies = Currency::where('is_active', true)->get();

        return view('content.settings.index', compact('settings', 'currencies'));
    }

    public function update(Request $request)
    {
        DB::transaction(function () use ($request) {

            foreach ($request->except('_token', '_method') as $group => $values) {

                if (!is_array($values)) {
                    continue;
                }

                foreach ($values as $key => $value) {

                    // Handle default currency
                    if ($group === 'general' && $key === 'currency') {
                        $this->syncDefaultCurrency($value);
                    }

                    Setting::set($group, $key, $value);
                }

                // Clear group cache
                Cache::forget("settings.group.{$group}");
            }
        });

        return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Sync currency default flag safely
     */
    protected function syncDefaultCurrency(string $currencyCode): void
    {
        Currency::where('is_default', true)->update(['is_default' => false]);

        Currency::where('code', $currencyCode)
            ->where('is_active', true)
            ->update(['is_default' => true]);
    }
}
