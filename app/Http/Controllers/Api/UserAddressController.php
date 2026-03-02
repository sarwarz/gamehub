<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * @group User Addresses
 *
 * APIs for managing saved billing and shipping addresses.
 * All endpoints require authentication.
 */
class UserAddressController extends Controller
{
    /**
     * List addresses
     *
     * Get all saved addresses for the authenticated user.
     * Addresses are ordered with default first, then most recent.
     *
     * @authenticated
     *
     * @queryParam type string Filter by type (billing, shipping, both). Example: billing
     *
     * @response 200 {"status":true,"message":"Addresses fetched","data":[{"id":1,"label":"Home","first_name":"John","last_name":"Doe","address_line1":"123 Main St","city":"New York","state":"NY","postal_code":"10001","country":"US","is_default":true,"type":"both"}]}
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'nullable|in:billing,shipping,both',
        ]);

        try {
            $addresses = UserAddress::where('user_id', $request->user()->id)
                ->when($request->type, fn ($q) => $q->where('type', $request->type))
                ->orderByDesc('is_default')
                ->latest()
                ->get();

            return $this->success($addresses, 'Addresses fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch addresses.', 500);
        }
    }

    /**
     * Get address
     *
     * Retrieve a specific saved address.
     *
     * @authenticated
     *
     * @urlParam id integer required Address ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Address fetched","data":{"id":1,"label":"Home","first_name":"John","last_name":"Doe","address_line1":"123 Main St","city":"New York","country":"US","is_default":true,"type":"both"}}
     * @response 404 {"status":false,"message":"Address not found."}
     */
    public function show($id): JsonResponse
    {
        try {
            $address = UserAddress::where('user_id', auth()->id())->find($id);

            if (!$address) {
                return $this->error('Address not found.', 404);
            }

            return $this->success($address, 'Address fetched');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch address.', 500);
        }
    }

    /**
     * Create address
     *
     * Save a new billing/shipping address. If this is the first address
     * or is_default is true, it becomes the default address automatically.
     *
     * @authenticated
     *
     * @bodyParam label string optional Label for the address. Example: Home
     * @bodyParam first_name string required First name. Example: John
     * @bodyParam last_name string required Last name. Example: Doe
     * @bodyParam company string optional Company name. Example: Acme Inc.
     * @bodyParam phone string optional Phone number. Example: +1234567890
     * @bodyParam address_line1 string required Street address. Example: 123 Main St
     * @bodyParam address_line2 string optional Apartment, suite, etc. Example: Apt 4B
     * @bodyParam city string required City. Example: New York
     * @bodyParam state string optional State/Province. Example: NY
     * @bodyParam postal_code string optional Postal/ZIP code. Example: 10001
     * @bodyParam country string required Country. Example: US
     * @bodyParam is_default boolean optional Set as default address. Example: true
     * @bodyParam type string optional Address type: billing, shipping, or both. Example: both
     *
     * @response 201 {"status":true,"message":"Address created successfully","data":{"id":1,"label":"Home","first_name":"John","last_name":"Doe","is_default":true}}
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'label'          => 'nullable|string|max:50',
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'company'        => 'nullable|string|max:150',
            'phone'          => 'nullable|string|max:30',
            'address_line1'  => 'required|string|max:255',
            'address_line2'  => 'nullable|string|max:255',
            'city'           => 'required|string|max:100',
            'state'          => 'nullable|string|max:100',
            'postal_code'    => 'nullable|string|max:20',
            'country'        => 'required|string|max:100',
            'is_default'     => 'nullable|boolean',
            'type'           => ['nullable', Rule::in(['billing', 'shipping', 'both'])],
        ]);

        try {
            if ($request->user()->addresses()->count() >= 10) {
                return $this->error('You can save a maximum of 10 addresses.', 422);
            }

            $data['user_id'] = $request->user()->id;
            $data['type']    = $data['type'] ?? 'both';

            $address = DB::transaction(function () use ($data) {
                $isFirst = !UserAddress::where('user_id', $data['user_id'])->exists();
                if ($isFirst || ($data['is_default'] ?? false)) {
                    UserAddress::where('user_id', $data['user_id'])->update(['is_default' => false]);
                    $data['is_default'] = true;
                }

                return UserAddress::create($data);
            });

            return $this->success($address, 'Address created successfully', 201);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to create address.', 500);
        }
    }

    /**
     * Update address
     *
     * Update an existing saved address.
     *
     * @authenticated
     *
     * @urlParam id integer required Address ID. Example: 1
     *
     * @bodyParam label string optional Label. Example: Office
     * @bodyParam first_name string optional First name. Example: John
     * @bodyParam last_name string optional Last name. Example: Doe
     * @bodyParam company string optional Company name.
     * @bodyParam phone string optional Phone number.
     * @bodyParam address_line1 string optional Street address.
     * @bodyParam address_line2 string optional Apartment, suite, etc.
     * @bodyParam city string optional City.
     * @bodyParam state string optional State/Province.
     * @bodyParam postal_code string optional Postal/ZIP code.
     * @bodyParam country string optional Country.
     * @bodyParam is_default boolean optional Set as default. Example: true
     * @bodyParam type string optional Address type: billing, shipping, both.
     *
     * @response 200 {"status":true,"message":"Address updated successfully","data":{"id":1,"label":"Office","is_default":true}}
     * @response 404 {"status":false,"message":"Address not found."}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'label'          => 'sometimes|string|max:50',
            'first_name'     => 'sometimes|string|max:100',
            'last_name'      => 'sometimes|string|max:100',
            'company'        => 'nullable|string|max:150',
            'phone'          => 'nullable|string|max:30',
            'address_line1'  => 'sometimes|string|max:255',
            'address_line2'  => 'nullable|string|max:255',
            'city'           => 'sometimes|string|max:100',
            'state'          => 'nullable|string|max:100',
            'postal_code'    => 'nullable|string|max:20',
            'country'        => 'sometimes|string|max:100',
            'is_default'     => 'nullable|boolean',
            'type'           => ['sometimes', Rule::in(['billing', 'shipping', 'both'])],
        ]);

        try {
            $address = UserAddress::where('user_id', $request->user()->id)->find($id);
            if (!$address) {
                return $this->error('Address not found.', 404);
            }

            DB::transaction(function () use ($request, $id, $address, $data) {
                if ($data['is_default'] ?? false) {
                    UserAddress::where('user_id', $request->user()->id)
                        ->where('id', '!=', $id)
                        ->update(['is_default' => false]);
                }

                $address->update($data);
            });

            return $this->success($address->fresh(), 'Address updated successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to update address.', 500);
        }
    }

    /**
     * Delete address
     *
     * Remove a saved address. If the deleted address was default,
     * the most recent remaining address becomes the new default.
     *
     * @authenticated
     *
     * @urlParam id integer required Address ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Address deleted successfully"}
     * @response 404 {"status":false,"message":"Address not found."}
     */
    public function destroy($id): JsonResponse
    {
        try {
            $address = UserAddress::where('user_id', auth()->id())->find($id);
            if (!$address) {
                return $this->error('Address not found.', 404);
            }

            $wasDefault = $address->is_default;

            DB::transaction(function () use ($address, $wasDefault) {
                $address->delete();

                if ($wasDefault) {
                    $next = UserAddress::where('user_id', auth()->id())->latest()->first();
                    $next?->update(['is_default' => true]);
                }
            });

            return $this->success(null, 'Address deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to delete address.', 500);
        }
    }

    /**
     * Set default address
     *
     * Mark an address as the default for the authenticated user.
     *
     * @authenticated
     *
     * @urlParam id integer required Address ID. Example: 1
     *
     * @response 200 {"status":true,"message":"Default address updated","data":{"id":1,"is_default":true}}
     * @response 404 {"status":false,"message":"Address not found."}
     */
    public function setDefault($id): JsonResponse
    {
        try {
            $address = UserAddress::where('user_id', auth()->id())->find($id);
            if (!$address) {
                return $this->error('Address not found.', 404);
            }

            DB::transaction(function () use ($address) {
                UserAddress::where('user_id', auth()->id())->update(['is_default' => false]);
                $address->update(['is_default' => true]);
            });

            return $this->success($address->fresh(), 'Default address updated');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to update default address.', 500);
        }
    }
}
