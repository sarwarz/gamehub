<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group User Profile
 *
 * APIs for managing the authenticated user's profile information,
 * including personal details and preferences.
 * For addresses, use the User Addresses API endpoints.
 */
class UserProfileController extends Controller
{
    /**
     * Get profile
     *
     * Retrieve the authenticated user's profile. If no profile exists yet,
     * an empty one is created automatically.
     *
     * @authenticated
     *
     * @response 200 {"status":true,"message":"Profile fetched successfully","data":{"id":1,"full_name":"John Doe","first_name":"John","last_name":"Doe","avatar":null,"dob":"1990-05-15","gender":"male","phone":"+1234567890","preferences":{"currency":"USD","language":"en","newsletter":true},"last_login":{"at":"2026-02-28T10:00:00.000000Z","ip":"192.168.1.1"}}}
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $profile = UserProfile::firstOrCreate(
                ['user_id' => $request->user()->id],
                []
            );

            return $this->success(
                $this->transform($profile),
                'Profile fetched successfully'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch profile.', 500);
        }
    }

    /**
     * Update profile
     *
     * Create or update the authenticated user's profile information.
     * All fields are optional — only send the fields you want to change.
     *
     * @authenticated
     *
     * @bodyParam first_name string optional First name. Example: John
     * @bodyParam last_name string optional Last name. Example: Doe
     * @bodyParam avatar string optional Avatar URL or path. Example: uploads/avatars/john.jpg
     * @bodyParam dob date optional Date of birth (YYYY-MM-DD). Example: 1990-05-15
     * @bodyParam gender string optional Gender: male, female, or other. Example: male
     * @bodyParam phone string optional Phone number. Example: +1234567890
     * @bodyParam alternate_phone string optional Secondary phone. Example: +0987654321
     * @bodyParam company string optional Company name. Example: Acme Inc.
     * @bodyParam tax_id string optional Tax ID. Example: US123456789
     * @bodyParam newsletter_subscribed boolean optional Opt in/out of newsletter. Example: true
     * @bodyParam preferred_currency string optional Preferred currency code. Example: USD
     * @bodyParam preferred_language string optional Preferred language code. Example: en
     *
     * @response 200 {"status":true,"message":"Profile updated successfully","data":{"id":1,"full_name":"John Doe","first_name":"John","last_name":"Doe"}}
     */
    public function storeOrUpdate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name'           => 'nullable|string|max:100',
            'last_name'            => 'nullable|string|max:100',
            'avatar'               => 'nullable|string|max:500',
            'dob'                  => 'nullable|date',
            'gender'               => 'nullable|in:male,female,other',
            'phone'                => 'nullable|string|max:30',
            'alternate_phone'      => 'nullable|string|max:30',
            'company'              => 'nullable|string|max:150',
            'tax_id'               => 'nullable|string|max:50',
            'newsletter_subscribed'=> 'nullable|boolean',
            'preferred_currency'   => 'nullable|string|max:10',
            'preferred_language'   => 'nullable|string|max:10',
        ]);

        try {
            $profile = UserProfile::updateOrCreate(
                ['user_id' => $request->user()->id],
                $data
            );

            return $this->success(
                $this->transform($profile),
                'Profile updated successfully'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to update profile.', 500);
        }
    }

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
            'alternate_phone' => $profile->alternate_phone,
            'company'   => $profile->company,
            'tax_id'    => $profile->tax_id,
            'preferences' => [
                'currency'   => $profile->preferred_currency,
                'language'   => $profile->preferred_language,
                'newsletter' => (bool) $profile->newsletter_subscribed,
            ],
            'last_login'  => [
                'at' => $profile->last_login_at,
                'ip' => $profile->last_login_ip,
            ],
        ];
    }
}
