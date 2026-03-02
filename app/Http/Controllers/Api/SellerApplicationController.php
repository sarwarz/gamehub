<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\Auth\SellerApplicationSubmittedNotification;
use App\Notifications\Auth\SellerApplicationAdminNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @group Seller Application
 *
 * APIs for customers to apply to become a seller on the marketplace.
 * The application goes through a review process before activation.
 */
class SellerApplicationController extends Controller
{
    /**
     * Apply to become a seller
     *
     * Submit a seller application. The authenticated user must not already
     * have a seller account. Applications start with "pending" status and
     * must be approved by an admin before the store becomes active.
     *
     * @authenticated
     *
     * @bodyParam store_name string required Store display name (max 255). Example: Digital Keys Hub
     * @bodyParam email string required Store contact email. Example: seller@example.com
     * @bodyParam phone string optional Contact phone number. Example: +1234567890
     * @bodyParam description string optional Store description (max 2000). Example: Premium digital game keys at the best prices.
     * @bodyParam country string required Country. Example: United States
     * @bodyParam state string optional State or province. Example: California
     * @bodyParam city string optional City. Example: Los Angeles
     * @bodyParam address string optional Street address. Example: 123 Market St
     * @bodyParam postal_code string optional Postal/zip code. Example: 90001
     * @bodyParam company_name string optional Legal company name. Example: Digital Keys LLC
     * @bodyParam registration_number string optional Business registration number. Example: LLC-2024-12345
     * @bodyParam vat_number string optional VAT/Tax ID number. Example: US123456789
     * @bodyParam tax_id string optional Tax identification number. Example: 12-3456789
     * @bodyParam website string optional Store website URL. Example: https://digitalkeyshub.com
     *
     * @response 201 {"status":true,"message":"Seller application submitted successfully. You will be notified once reviewed.","data":{"id":1,"store_name":"Digital Keys Hub","slug":"digital-keys-hub","status":"pending","created_at":"2026-02-28T12:00:00.000000Z"}}
     * @response 409 {"status":false,"message":"You already have a seller account."}
     * @response 422 {"status":false,"message":"Validation error"}
     */
    public function apply(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_name'          => 'required|string|max:255',
            'email'               => 'required|email|max:255',
            'phone'               => 'nullable|string|max:30',
            'description'         => 'nullable|string|max:2000',
            'country'             => 'required|string|max:100',
            'state'               => 'nullable|string|max:100',
            'city'                => 'nullable|string|max:100',
            'address'             => 'nullable|string|max:500',
            'postal_code'         => 'nullable|string|max:20',
            'company_name'        => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'vat_number'          => 'nullable|string|max:50',
            'tax_id'              => 'nullable|string|max:50',
            'website'             => 'nullable|url|max:255',
        ]);

        try {
            $vendorSettings = Setting::group('vendor');

            if (isset($vendorSettings['registration_enabled']) && !$vendorSettings['registration_enabled']) {
                return $this->error('Seller registration is currently disabled.', 403);
            }

            $user = $request->user();

            $existing = Seller::where('user_id', $user->id)->first();
            if ($existing) {
                $statusMessages = [
                    'pending'   => 'Your seller application is already under review.',
                    'active'    => 'You already have an active seller account.',
                    'suspended' => 'Your seller account is suspended. Please contact support.',
                    'rejected'  => 'Your previous application was rejected. Please contact support to re-apply.',
                ];
                return $this->error($statusMessages[$existing->status] ?? 'You already have a seller account.', 409);
            }

            $slug = Str::slug($data['store_name']);
            $originalSlug = $slug;
            $counter = 1;
            while (Seller::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            if (!empty($vendorSettings['require_documents'])) {
                $request->validate([
                    'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                ]);
            }

            $data['user_id']     = $user->id;
            $data['slug']        = $slug;
            $data['is_verified'] = false;

            $autoApprove = !empty($vendorSettings['auto_approve']);
            $data['status'] = $autoApprove ? 'active' : 'pending';

            $seller = Seller::create($data);

            if ($autoApprove) {
                $seller->syncUserRoles();
            }

            $notifSettings = Setting::group('notifications');

            if (!empty($notifSettings['seller_registered'] ?? true)) {
                try {
                    $user->notify(new SellerApplicationSubmittedNotification($seller));
                } catch (\Throwable $e) {
                    \Log::warning('Seller application confirmation email failed: ' . $e->getMessage());
                }

                try {
                    $admins = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['superadmin', 'admin']))->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new SellerApplicationAdminNotification($seller));
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Seller application admin notification failed: ' . $e->getMessage());
                }
            }

            return $this->success([
                'id'         => $seller->id,
                'store_name' => $seller->store_name,
                'slug'       => $seller->slug,
                'status'     => $seller->status,
                'created_at' => $seller->created_at,
            ], 'Seller application submitted successfully. You will be notified once reviewed.', 201);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Check application status
     *
     * Get the current status of the authenticated user's seller application.
     * Returns application details and current review status.
     *
     * @authenticated
     *
     * @response 200 {"status":true,"message":"Application status fetched.","data":{"id":1,"store_name":"Digital Keys Hub","slug":"digital-keys-hub","status":"pending","is_verified":false,"created_at":"2026-02-28T12:00:00.000000Z"}}
     * @response 404 {"status":false,"message":"No seller application found. Apply to become a seller first."}
     */
    public function status(Request $request): JsonResponse
    {
        try {
            $seller = Seller::where('user_id', $request->user()->id)->first();

            if (!$seller) {
                return $this->error('No seller application found. Apply to become a seller first.', 404);
            }

            return $this->success([
                'id'                  => $seller->id,
                'store_name'          => $seller->store_name,
                'slug'                => $seller->slug,
                'email'               => $seller->email,
                'phone'               => $seller->phone,
                'description'         => $seller->description,
                'country'             => $seller->country,
                'state'               => $seller->state,
                'city'                => $seller->city,
                'company_name'        => $seller->company_name,
                'website'             => $seller->website,
                'status'              => $seller->status,
                'is_verified'         => $seller->is_verified,
                'created_at'          => $seller->created_at,
                'updated_at'          => $seller->updated_at,
            ], 'Application status fetched.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }

    /**
     * Update pending application
     *
     * Update a seller application that is still in "pending" status.
     * Once approved or rejected, applications cannot be modified.
     *
     * @authenticated
     *
     * @bodyParam store_name string optional Updated store name. Example: My New Store Name
     * @bodyParam email string optional Updated contact email. Example: newemail@example.com
     * @bodyParam phone string optional Updated phone. Example: +1987654321
     * @bodyParam description string optional Updated description. Example: Updated store description.
     * @bodyParam country string optional Updated country. Example: United Kingdom
     * @bodyParam state string optional Updated state. Example: London
     * @bodyParam city string optional Updated city. Example: Westminster
     * @bodyParam address string optional Updated address. Example: 456 High Street
     * @bodyParam postal_code string optional Updated postal code. Example: SW1A 1AA
     * @bodyParam company_name string optional Updated company name. Example: New Company Ltd
     * @bodyParam registration_number string optional Updated registration number.
     * @bodyParam vat_number string optional Updated VAT number.
     * @bodyParam tax_id string optional Updated tax ID.
     * @bodyParam website string optional Updated website URL.
     *
     * @response 200 {"status":true,"message":"Application updated successfully.","data":{}}
     * @response 403 {"status":false,"message":"Only pending applications can be updated."}
     * @response 404 {"status":false,"message":"No seller application found."}
     */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_name'          => 'sometimes|string|max:255',
            'email'               => 'sometimes|email|max:255',
            'phone'               => 'nullable|string|max:30',
            'description'         => 'nullable|string|max:2000',
            'country'             => 'sometimes|string|max:100',
            'state'               => 'nullable|string|max:100',
            'city'                => 'nullable|string|max:100',
            'address'             => 'nullable|string|max:500',
            'postal_code'         => 'nullable|string|max:20',
            'company_name'        => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'vat_number'          => 'nullable|string|max:50',
            'tax_id'              => 'nullable|string|max:50',
            'website'             => 'nullable|url|max:255',
        ]);

        try {
            $seller = Seller::where('user_id', $request->user()->id)->first();

            if (!$seller) {
                return $this->error('No seller application found.', 404);
            }

            if ($seller->status !== 'pending') {
                return $this->error('Only pending applications can be updated.', 403);
            }

            if (isset($data['store_name']) && $data['store_name'] !== $seller->store_name) {
                $slug = Str::slug($data['store_name']);
                $originalSlug = $slug;
                $counter = 1;
                while (Seller::where('slug', $slug)->where('id', '!=', $seller->id)->exists()) {
                    $slug = $originalSlug . '-' . $counter++;
                }
                $data['slug'] = $slug;
            }

            $seller->update($data);

            return $this->success($seller->fresh()->only([
                'id', 'store_name', 'slug', 'email', 'phone', 'description',
                'country', 'state', 'city', 'company_name', 'website',
                'status', 'is_verified', 'created_at', 'updated_at',
            ]), 'Application updated successfully.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Something went wrong');
        }
    }
}
