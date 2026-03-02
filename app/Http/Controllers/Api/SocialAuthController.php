<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * @group Authentication
 *
 * Social login endpoints for Google, Facebook, GitHub, and Twitter authentication.
 * The frontend obtains an OAuth access token from the provider's SDK,
 * then exchanges it here for a Sanctum bearer token.
 *
 * ## Flow
 *
 * 1. Frontend calls `GET /settings/bootstrap` → checks `allow_social_login` and `social_providers`
 * 2. Frontend shows buttons only for enabled providers
 * 3. User authenticates with provider → frontend receives an access token
 * 4. Frontend sends the access token to `POST /auth/social/{provider}`
 * 5. Backend validates the token with the provider, creates or links the user
 * 6. Returns a Sanctum token + user object
 *
 * ## Supported Providers
 *
 * | Provider | Slug |
 * |----------|------|
 * | Google   | `google` |
 * | Facebook | `facebook` |
 * | GitHub   | `github` |
 * | Twitter  | `twitter` |
 *
 * > Providers must be enabled in Admin → Settings → Registration → Social Login.
 */
class SocialAuthController extends Controller
{
    private const SUPPORTED_PROVIDERS = ['google', 'facebook', 'github', 'twitter'];

    /**
     * Social login
     *
     * Exchange a provider access token for a Sanctum bearer token.
     * If the user doesn't exist, a new account is created automatically
     * with the role configured in Admin → Registration → Auto-Assign Role.
     * If the email already exists with password auth, the social account is linked.
     *
     * > Social login must be enabled in admin settings. The specific provider
     * > must also be checked in the Social Providers list.
     *
     * @unauthenticated
     *
     * @urlParam provider string required The social provider: google, facebook, github, or twitter. Example: google
     *
     * @bodyParam access_token string required OAuth access token from the provider. Example: ya29.A0AfH6SMBZ...
     *
     * @response 200 {"status":true,"message":"Login successful","data":{"token":"1|abc123...","user":{"id":1,"name":"John Doe","email":"john@example.com","avatar":"https://...","email_verified":true,"roles":["customer"]}}}
     * @response 422 {"status":false,"message":"Social login is disabled"}
     * @response 422 {"status":false,"message":"Google login is not enabled"}
     * @response 422 {"status":false,"message":"Invalid provider. Supported: google, facebook, github, twitter"}
     */
    public function handle(Request $request, string $provider): JsonResponse
    {
        if (!in_array($provider, self::SUPPORTED_PROVIDERS)) {
            return $this->error('Invalid provider. Supported: ' . implode(', ', self::SUPPORTED_PROVIDERS), 422);
        }

        $allowSocialLogin = (bool) Setting::get('registration', 'allow_social_login', false);
        if (!$allowSocialLogin) {
            return $this->error('Social login is disabled.', 422);
        }

        $enabledProviders = Setting::get('registration', 'social_providers', []);
        if (is_string($enabledProviders)) {
            $enabledProviders = json_decode($enabledProviders, true) ?? [];
        }

        if (!in_array($provider, (array) $enabledProviders)) {
            return $this->error(ucfirst($provider) . ' login is not enabled.', 422);
        }

        $request->validate([
            'access_token' => 'required|string',
        ]);

        try {
            $socialUser = Socialite::driver($provider)
                ->stateless()
                ->userFromToken($request->access_token);

            if (!$socialUser->getEmail()) {
                return $this->error('Unable to retrieve email from ' . $provider . '. Please ensure your account has a public email.', 422);
            }

            $autoAssignRole = Setting::get('registration', 'auto_assign_role', 'customer');

            $user = DB::transaction(function () use ($socialUser, $provider, $autoAssignRole) {
                $user = User::where('email', $socialUser->getEmail())->first();

                if ($user) {
                    if (!$user->social_provider) {
                        $user->update([
                            'social_provider' => $provider,
                            'social_id'       => $socialUser->getId(),
                            'avatar'          => $socialUser->getAvatar(),
                        ]);
                    }
                } else {
                    $user = User::create([
                        'name'              => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                        'email'             => $socialUser->getEmail(),
                        'password'          => Hash::make(Str::random(32)),
                        'social_provider'   => $provider,
                        'social_id'         => $socialUser->getId(),
                        'avatar'            => $socialUser->getAvatar(),
                        'email_verified_at' => now(),
                        'is_active'         => true,
                    ]);

                    $role = Role::where('name', $autoAssignRole)->first();
                    if ($role) {
                        $user->roles()->attach($role->id);
                    }
                }

                return $user;
            });

            if (!$user->is_active) {
                return $this->error('Your account has been deactivated.', 403);
            }

            $token = $user->createToken('social-auth')->plainTextToken;

            return $this->success([
                'token' => $token,
                'user'  => [
                    'id'             => $user->id,
                    'name'           => $user->name,
                    'email'          => $user->email,
                    'avatar'         => $user->avatar,
                    'email_verified' => (bool) $user->email_verified_at,
                    'roles'          => $user->roles->pluck('name'),
                ],
            ], 'Login successful');

        } catch (\Throwable $e) {
            report($e);
            return $this->error('Social authentication failed. Please try again.', 500);
        }
    }
}
