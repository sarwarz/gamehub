<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;

/**
 * @group User Profile
 *
 * APIs for managing authenticated user's profile
 * information and preferences.
 */
class UserProfileController extends Controller
{
    /**
     * Get user profile
     *
     * Retrieve the authenticated user's profile.
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Profile fetched successfully",
     *   "data": {
     *     "full_name": "John Doe",
     *     "country": "US"
     *   }
     * }
     */
    public function show(Request $request)
    {
        $profile = UserProfile::firstOrCreate(
            ['user_id' => $request->user()->id],
            []
        );

        return $this->successResponse(
            $this->transform($profile),
            'Profile fetched successfully'
        );
    }

    /**
     * Create or update profile
     *
     * Create or update authenticated user's profile.
     *
     * @authenticated
     *
     * @bodyParam first_name string Optional. First name. Example: John
     * @bodyParam last_name string Optional. Last name. Example: Doe
     * @bodyParam phone string Optional Phone number.
     * @bodyParam dob string Optional Date of birth (YYYY-MM-DD). Example: 1995-01-01
     * @bodyParam gender string Optional Gender. Example: male
     * @bodyParam address_line1 string Optional Address line 1.
     * @bodyParam city string Optional City.
     * @bodyParam country string Optional Country. Example: US
     * @bodyParam preferred_currency string Optional Currency code. Example: USD
     * @bodyParam preferred_language string Optional Language code. Example: en
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Profile updated successfully"
     * }
     */
    public function storeOrUpdate(Request $request)
    {
        $data = $request->validate([
            'first_name'          => 'nullable|string|max:100',
            'last_name'           => 'nullable|string|max:100',
            'avatar'              => 'nullable|string',
            'dob'                 => 'nullable|date',
            'gender'              => 'nullable|in:male,female,other',
            'phone'               => 'nullable|string|max:30',
            'alternate_phone'     => 'nullable|string|max:30',
            'address_line1'       => 'nullable|string|max:255',
            'address_line2'       => 'nullable|string|max:255',
            'city'                => 'nullable|string|max:100',
            'state'               => 'nullable|string|max:100',
            'postal_code'         => 'nullable|string|max:20',
            'country'             => 'nullable|string|max:100',
            'company'             => 'nullable|string|max:150',
            'tax_id'              => 'nullable|string|max:50',
            'newsletter_subscribed'=> 'nullable|boolean',
            'preferred_currency'  => 'nullable|string|max:10',
            'preferred_language'  => 'nullable|string|max:10',
        ]);

        $profile = UserProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return $this->successResponse(
            $this->transform($profile),
            'Profile updated successfully'
        );
    }

    /* --------------------------------
     | Data Transformer
     |-------------------------------- */

    protected function transform(UserProfile $profile): array
    {
        return [
            'id'        => $profile->id,
            'full_name' => $profile->full_name,
            'first_name'=> $profile->first_name,
            'last_name' => $profile->last_name,
            'avatar'    => $profile->avatar,
            'dob'       => $profile->dob,
            'gender'    => $profile->gender,
            'phone'     => $profile->phone,
            'address'   => [
                'line1'  => $profile->address_line1,
                'line2'  => $profile->address_line2,
                'city'   => $profile->city,
                'state'  => $profile->state,
                'postal' => $profile->postal_code,
                'country'=> $profile->country,
            ],
            'preferences' => [
                'currency' => $profile->preferred_currency,
                'language' => $profile->preferred_language,
                'newsletter' => (bool) $profile->newsletter_subscribed,
            ],
            'is_verified' => (bool) $profile->is_verified,
            'last_login'  => [
                'at' => $profile->last_login_at,
                'ip' => $profile->last_login_ip,
            ],
        ];
    }

    /* --------------------------------
     | API Response Helpers
     |-------------------------------- */

    protected function successResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function errorResponse($message, $code = 400)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
        ], $code);
    }
}
