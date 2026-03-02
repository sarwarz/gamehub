<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AffiliateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Affiliate Tracking
 *
 * Public endpoints for tracking affiliate referral clicks.
 */
class AffiliateTrackingController extends Controller
{
    /**
     * Track a referral click
     *
     * Records a click from a referral link. Call this when a user arrives via ?ref= parameter.
     *
     * @bodyParam ref string required The referral code. Example: AB12CD34
     * @bodyParam landing_page string The page URL the user landed on. Example: https://example.com/products
     *
     * @response 200 { "status": true, "message": "Referral tracked." }
     * @response 200 { "status": true, "message": "Invalid or duplicate referral." }
     */
    public function track(Request $request): JsonResponse
    {
        $request->validate([
            'ref'          => 'required|string|max:20',
            'landing_page' => 'nullable|string|max:500',
        ]);

        $referral = AffiliateService::trackClick(
            $request->ref,
            $request->ip(),
            $request->userAgent(),
            $request->landing_page
        );

        if (!$referral) {
            return $this->success(null, 'Invalid or duplicate referral.');
        }

        return $this->success(['referral_id' => $referral->id], 'Referral tracked.');
    }
}
