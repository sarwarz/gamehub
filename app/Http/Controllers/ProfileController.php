<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Currency;
use App\Models\UserAddress;
use App\Models\UserProfile;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function superAdminLogin()
    {
        $superAdminRole = Role::where('name', 'superadmin')->first();

        if (!$superAdminRole) {
            abort(403, 'Super Admin role not found');
        }

        $superAdmin = $superAdminRole->users()->first();

        if (!$superAdmin) {
            abort(403, 'No user assigned to Super Admin role');
        }

        if (!$superAdmin->hasVerifiedEmail()) {
            $superAdmin->markEmailAsVerified();
            $superAdmin->update(['is_verified' => true]);
        }

        Auth::login($superAdmin);

        return redirect()->route('dashboard');
    }

    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->load(['profile', 'roles', 'addresses']);

        $profile    = $user->profile ?? new UserProfile();
        $addresses  = $user->addresses()->orderByDesc('is_default')->latest()->get();
        $currencies = Currency::where('is_active', true)->orderBy('code')->get();

        return view('profile.edit', compact('user', 'profile', 'addresses', 'currencies'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
        ]);

        $profileData = $request->validate([
            'first_name'            => ['nullable', 'string', 'max:100'],
            'last_name'             => ['nullable', 'string', 'max:100'],
            'avatar_file'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'remove_avatar'         => ['nullable', 'boolean'],
            'dob'                   => ['nullable', 'date'],
            'gender'                => ['nullable', 'in:male,female,other'],
            'phone'                 => ['nullable', 'string', 'max:30'],
            'alternate_phone'       => ['nullable', 'string', 'max:30'],
            'company'               => ['nullable', 'string', 'max:150'],
            'tax_id'                => ['nullable', 'string', 'max:50'],
            'newsletter_subscribed' => ['nullable', 'boolean'],
            'preferred_currency'    => ['nullable', 'string', 'max:10'],
            'preferred_language'    => ['nullable', 'string', 'max:10'],
        ]);

        if (!$request->has('newsletter_subscribed')) {
            $profileData['newsletter_subscribed'] = false;
        }

        $existingProfile = UserProfile::where('user_id', $request->user()->id)->first();

        if ($request->hasFile('avatar_file')) {
            if ($existingProfile?->avatar && File::exists(public_path($existingProfile->avatar))) {
                File::delete(public_path($existingProfile->avatar));
            }

            $file = $request->file('avatar_file');
            $dir = public_path('uploads/avatars');
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $filename = 'avatar_' . $request->user()->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $profileData['avatar'] = 'uploads/avatars/' . $filename;
        } elseif (!empty($profileData['remove_avatar'])) {
            if ($existingProfile?->avatar && File::exists(public_path($existingProfile->avatar))) {
                File::delete(public_path($existingProfile->avatar));
            }
            $profileData['avatar'] = null;
        }

        unset($profileData['avatar_file'], $profileData['remove_avatar']);

        $user = $request->user();
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        if ($request->user()->addresses()->count() >= 10) {
            return Redirect::route('profile.edit', ['#address'])
                ->with('error', 'You can save a maximum of 10 addresses.');
        }

        $data = $request->validate([
            'label'         => 'nullable|string|max:50',
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'company'       => 'nullable|string|max:150',
            'phone'         => 'nullable|string|max:30',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city'          => 'required|string|max:100',
            'state'         => 'nullable|string|max:100',
            'postal_code'   => 'nullable|string|max:20',
            'country'       => 'required|string|max:100',
            'is_default'    => 'nullable|boolean',
            'type'          => ['nullable', Rule::in(['billing', 'shipping', 'both'])],
        ]);

        $data['user_id'] = $request->user()->id;
        $data['type']    = $data['type'] ?? 'both';

        DB::transaction(function () use ($data) {
            $isFirst = !UserAddress::where('user_id', $data['user_id'])->exists();
            if ($isFirst || ($data['is_default'] ?? false)) {
                UserAddress::where('user_id', $data['user_id'])->update(['is_default' => false]);
                $data['is_default'] = true;
            }
            UserAddress::create($data);
        });

        return Redirect::route('profile.edit', ['#address'])->with('status', 'address-created');
    }

    public function updateAddress(Request $request, UserAddress $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $data = $request->validate([
            'label'         => 'nullable|string|max:50',
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'company'       => 'nullable|string|max:150',
            'phone'         => 'nullable|string|max:30',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city'          => 'required|string|max:100',
            'state'         => 'nullable|string|max:100',
            'postal_code'   => 'nullable|string|max:20',
            'country'       => 'required|string|max:100',
            'is_default'    => 'nullable|boolean',
            'type'          => ['nullable', Rule::in(['billing', 'shipping', 'both'])],
        ]);

        DB::transaction(function () use ($address, $data, $request) {
            if ($data['is_default'] ?? false) {
                UserAddress::where('user_id', $request->user()->id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }
            $address->update($data);
        });

        return Redirect::route('profile.edit', ['#address'])->with('status', 'address-updated');
    }

    public function destroyAddress(Request $request, UserAddress $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $wasDefault = $address->is_default;

        DB::transaction(function () use ($address, $wasDefault, $request) {
            $address->delete();
            if ($wasDefault) {
                $next = UserAddress::where('user_id', $request->user()->id)->latest()->first();
                $next?->update(['is_default' => true]);
            }
        });

        return Redirect::route('profile.edit', ['#address'])->with('status', 'address-deleted');
    }

    public function setDefaultAddress(Request $request, UserAddress $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        DB::transaction(function () use ($address, $request) {
            UserAddress::where('user_id', $request->user()->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return Redirect::route('profile.edit', ['#address'])->with('status', 'default-address-updated');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return Redirect::route('profile.edit')->with('status', 'password-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
