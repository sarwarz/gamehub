<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\User;
use App\Models\Setting;
use App\Services\CaptchaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Events\PasswordReset;
use App\Notifications\Auth\WelcomeNotification;

/**
 * @group Authentication
 *
 * Complete authentication system using **Laravel Sanctum** Bearer tokens.
 * Covers registration, email verification, login, password management,
 * and account deletion.
 *
 * ## Complete Auth Flow
 *
 * ```
 * ┌──────────────────────────────────────────────────────────────────┐
 * │                    REGISTRATION & VERIFICATION                   │
 * ├──────────────────────────────────────────────────────────────────┤
 * │                                                                  │
 * │  1. POST /auth/register                                          │
 * │     body: { name, email, password, password_confirmation }        │
 * │     ← { token, user: { email_verified: false } }                │
 * │                                                                  │
 * │  2. User receives verification email with a signed link           │
 * │     (if require_email_verification is enabled in admin settings)  │
 * │                                                                  │
 * │  3. GET /auth/verify-email/{id}/{hash}                           │
 * │     ← { message: "Email verified successfully." }                │
 * │                                                                  │
 * │  4. (Optional) POST /auth/send-verification  [authenticated]     │
 * │     ← Resends the verification email if not yet verified         │
 * │                                                                  │
 * ├──────────────────────────────────────────────────────────────────┤
 * │                          LOGIN                                   │
 * ├──────────────────────────────────────────────────────────────────┤
 * │                                                                  │
 * │  5. POST /auth/login                                             │
 * │     body: { email, password }                                     │
 * │     ← { token, user: { id, name, email, email_verified } }      │
 * │                                                                  │
 * │  6. Store the token → use in Authorization header for all         │
 * │     authenticated requests                                       │
 * │                                                                  │
 * ├──────────────────────────────────────────────────────────────────┤
 * │                    PASSWORD RECOVERY                              │
 * ├──────────────────────────────────────────────────────────────────┤
 * │                                                                  │
 * │  7. POST /auth/forgot-password                                   │
 * │     body: { email }                                               │
 * │     ← Sends password reset email with token                      │
 * │                                                                  │
 * │  8. POST /auth/reset-password                                    │
 * │     body: { email, token, password, password_confirmation }       │
 * │     ← Password updated, all tokens revoked                      │
 * │                                                                  │
 * └──────────────────────────────────────────────────────────────────┘
 * ```
 *
 * ## Next.js Integration Example
 *
 * ```javascript
 * // ─── Registration ───
 * const { token, user } = await api.post('/auth/register', {
 *   name: 'John Doe',
 *   email: 'john@example.com',
 *   password: 'SecurePass123!',
 *   password_confirmation: 'SecurePass123!',
 *   captcha_token: captchaToken,  // if CAPTCHA enabled
 * });
 * api.setToken(token);
 * // → Show "Check your email for verification link"
 *
 * // ─── Login ───
 * const { token, user } = await api.post('/auth/login', {
 *   email: 'john@example.com',
 *   password: 'SecurePass123!',
 *   captcha_token: captchaToken,
 * });
 * api.setToken(token);
 * if (!user.email_verified) {
 *   // → Redirect to "verify your email" page
 * }
 *
 * // ─── Resend Verification ───
 * await api.post('/auth/send-verification');
 *
 * // ─── Forgot Password ───
 * await api.post('/auth/forgot-password', {
 *   email: 'john@example.com',
 *   captcha_token: captchaToken,
 * });
 *
 * // ─── Reset Password (from email link) ───
 * await api.post('/auth/reset-password', {
 *   email: 'john@example.com',
 *   token: tokenFromEmailLink,
 *   password: 'NewSecure456!',
 *   password_confirmation: 'NewSecure456!',
 * });
 * ```
 *
 * ## CAPTCHA Protection
 *
 * When CAPTCHA is enabled (check `GET /settings/bootstrap` → `captcha`),
 * these endpoints require a `captcha_token` field:
 * - `POST /auth/register`
 * - `POST /auth/login`
 * - `POST /auth/forgot-password`
 *
 * Check `captcha.provider` to determine which widget to render:
 * - `"recaptcha"` → Google reCAPTCHA v3 (use `grecaptcha.execute()`)
 * - `"turnstile"` → Cloudflare Turnstile widget
 * - `"none"` → Don't send `captcha_token`
 *
 * ## Login Throttling
 *
 * Login attempts are rate-limited per email+IP combination.
 * Default: **5 attempts** then locked for **15 minutes** (configurable by admin).
 * When locked out, the response is `429` with a `Retry-After` header.
 *
 * ## Password Requirements
 *
 * Password rules are configurable by admin via Settings → Security:
 * - Minimum length (default: 8)
 * - Require uppercase letter (optional)
 * - Require number (optional)
 * - Require special character (optional)
 *
 * When validation fails, the error response includes specific messages
 * explaining which rules were not met.
 *
 * ## Token Management
 *
 * | Action | What happens to tokens |
 * |--------|----------------------|
 * | Register | New token created |
 * | Login | New token created (old tokens remain valid) |
 * | Logout | Current token revoked |
 * | Change password | All tokens revoked except current |
 * | Reset password | All tokens revoked |
 * | Delete account | All tokens revoked, account deleted |
 *
 * ## Email Verification Deep Link
 *
 * The verification email contains a link like:
 * ```
 * https://your-domain.com/api/v1/auth/verify-email/{id}/{hash}?signature=...
 * ```
 *
 * **For SPA/Next.js apps**: Configure your backend to redirect the verification
 * URL to your frontend (e.g., `https://app.example.com/verify?status=success`).
 * The API endpoint verifies the hash and marks the email as verified.
 *
 * ## Admin-Controlled Settings
 *
 * These auth behaviors are configurable via the admin panel:
 *
 * | Setting | Effect |
 * |---------|--------|
 * | `registration_enabled` | `false` → register returns `403` |
 * | `require_email_verification` | `false` → no verification email sent |
 * | `welcome_email_enabled` | `true` → sends welcome email on register |
 * | `auto_assign_role` | Role assigned on registration (default: `customer`) |
 * | `max_login_attempts` | Attempts before lockout (default: 5) |
 * | `lockout_duration_minutes` | Lockout duration (default: 15) |
 * | `password_min_length` | Minimum password length (default: 8) |
 * | `password_require_uppercase` | Require uppercase letter |
 * | `password_require_number` | Require digit |
 * | `password_require_symbol` | Require special character |
 */
class AuthController extends Controller
{
    /**
     * Register
     *
     * Create a new user account and receive an API token.
     * A verification email will be sent to confirm the email address.
     *
     * @unauthenticated
     *
     * @bodyParam name string required Full name. Example: John Doe
     * @bodyParam email string required Email address (must be unique). Example: john@example.com
     * @bodyParam password string required Password (min 8 characters). Example: password123
     * @bodyParam password_confirmation string required Must match password. Example: password123
     * @bodyParam captcha_token string optional CAPTCHA token for reCAPTCHA v3 or Cloudflare Turnstile (required when CAPTCHA is enabled). No-example
     *
     * @response 201 {"status":true,"message":"Registration successful. Please verify your email.","data":{"token":"1|abc123...","user":{"id":1,"name":"John Doe","email":"john@example.com","email_verified":false}}}
     * @response 422 {"status":false,"message":"The email has already been taken."}
     */
    public function register(Request $request): JsonResponse
    {
        $regSettings = Setting::group('registration');
        if (isset($regSettings['registration_enabled']) && !$regSettings['registration_enabled']) {
            return $this->error('Registration is currently disabled.', 403);
        }

        if ($error = CaptchaService::check($request->input('captcha_token'), 'register')) {
            return $this->error($error, 422);
        }

        $passwordRules = $this->buildPasswordRules();

        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', ...$passwordRules],
        ];

        if (!empty($regSettings['require_phone_number'])) {
            $rules['phone'] = 'required|string|max:20';
        }

        if (!empty($regSettings['min_age_required']) && (int) $regSettings['min_age_required'] > 0) {
            $minAge = (int) $regSettings['min_age_required'];
            $rules['date_of_birth'] = "required|date|before_or_equal:" . now()->subYears($minAge)->toDateString();
        }

        $data = $request->validate($rules);

        try {
            $user = User::create([
                'name'        => $data['name'],
                'email'       => $data['email'],
                'password'    => $data['password'],
                'is_active'   => true,
                'is_verified' => false,
            ]);

            $roleName = $regSettings['auto_assign_role'] ?? 'customer';
            $role = Role::where('name', $roleName)->first() ?? Role::where('name', 'customer')->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }

            $requireVerification = $regSettings['require_email_verification'] ?? true;
            if ($requireVerification) {
                $user->sendEmailVerificationNotification();
            }

            $welcomeEnabled = $regSettings['welcome_email_enabled'] ?? true;
            if ($welcomeEnabled) {
                try {
                    $user->notify(new WelcomeNotification);
                } catch (\Throwable $e) {
                    \Log::warning('Welcome email failed: ' . $e->getMessage());
                }
            }

            $message = $requireVerification
                ? 'Registration successful. Please verify your email.'
                : 'Registration successful.';

            return $this->success($this->token($user), $message, 201);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to register user.', 500);
        }
    }

    /**
     * Login
     *
     * Authenticate with email and password to receive an API token.
     * Disabled accounts will receive a 403 error.
     *
     * @unauthenticated
     *
     * @bodyParam email string required Email address. Example: john@example.com
     * @bodyParam password string required Password. Example: password123
     * @bodyParam captcha_token string optional CAPTCHA token for reCAPTCHA v3 or Cloudflare Turnstile (required when CAPTCHA is enabled). No-example
     *
     * @response 200 {"status":true,"message":"Login successful","data":{"token":"1|abc123...","user":{"id":1,"name":"John Doe","email":"john@example.com","email_verified":true}}}
     * @response 401 {"status":false,"message":"Invalid credentials"}
     * @response 403 {"status":false,"message":"Account is disabled"}
     */
    public function login(Request $request): JsonResponse
    {
        if ($error = CaptchaService::check($request->input('captcha_token'), 'login')) {
            return $this->error($error, 422);
        }

        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        try {
            $securitySettings = Setting::group('security');
            $maxAttempts = (int) ($securitySettings['max_login_attempts'] ?? 5);
            $lockoutMinutes = (int) ($securitySettings['lockout_duration_minutes'] ?? 15);

            $throttleKey = 'login:' . Str::lower($credentials['email']) . '|' . $request->ip();

            if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
                $seconds = RateLimiter::availableIn($throttleKey);
                return $this->error(
                    "Too many login attempts. Please try again in {$seconds} seconds.",
                    429
                );
            }

            $user = User::where('email', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                RateLimiter::hit($throttleKey, $lockoutMinutes * 60);
                return $this->error('Invalid credentials', 401);
            }

            if (!$user->is_active) {
                return $this->error('Account is disabled', 403);
            }

            RateLimiter::clear($throttleKey);

            $user->profile()?->updateOrCreate(
                ['user_id' => $user->id],
                ['last_login_at' => now(), 'last_login_ip' => $request->ip()]
            );

            return $this->success($this->token($user), 'Login successful');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to authenticate.', 500);
        }
    }

    /**
     * Logout
     *
     * Revoke the current API token. The token used in the request
     * will no longer be valid for authentication.
     *
     * @authenticated
     *
     * @response 200 {"status":true,"message":"Logged out successfully","data":null}
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->success(null, 'Logged out successfully');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to logout.', 500);
        }
    }

    /**
     * Send verification email
     *
     * Resend the email verification link. If the user's email is already
     * verified, a message will indicate so.
     *
     * @authenticated
     *
     * @response 200 {"status":true,"message":"Verification link sent to your email.","data":null}
     * @response 200 {"status":true,"message":"Email already verified.","data":null}
     */
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasVerifiedEmail()) {
                return $this->success(null, 'Email already verified.');
            }

            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                report($e);
                return $this->error('Failed to send verification email.', 500);
            }

            return $this->success(null, 'Verification link sent to your email.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to process verification request.', 500);
        }
    }

    /**
     * Verify email
     *
     * Verify the user's email address using the signed URL from the
     * verification email. The `id` and `hash` are extracted from the link.
     *
     * @unauthenticated
     *
     * @urlParam id integer required The user ID. Example: 1
     * @urlParam hash string required SHA1 hash of the email. Example: abc123def456
     *
     * @response 200 {"status":true,"message":"Email verified successfully.","data":null}
     * @response 400 {"status":false,"message":"Invalid or expired verification link."}
     * @response 200 {"status":true,"message":"Email already verified.","data":null}
     */
    public function verifyEmail(Request $request, $id, $hash): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->error('User not found.', 404);
            }

            if ($user->hasVerifiedEmail()) {
                return $this->success(null, 'Email already verified.');
            }

            if (!hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
                return $this->error('Invalid or expired verification link.', 400);
            }

            $user->markEmailAsVerified();
            $user->update(['is_verified' => true]);

            event(new Verified($user));

            return $this->success(null, 'Email verified successfully.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to verify email.', 500);
        }
    }

    /**
     * Forgot password
     *
     * Send a password reset link to the given email address.
     * The link will expire after 60 minutes.
     *
     * @unauthenticated
     *
     * @bodyParam email string required Email address. Example: john@example.com
     * @bodyParam captcha_token string optional CAPTCHA token for reCAPTCHA v3 or Cloudflare Turnstile (required when CAPTCHA is enabled). No-example
     *
     * @response 200 {"status":true,"message":"Password reset link sent to your email.","data":null}
     * @response 422 {"status":false,"message":"We can't find a user with that email address."}
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        if ($error = CaptchaService::check($request->input('captcha_token'), 'forgot_password')) {
            return $this->error($error, 422);
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            Password::sendResetLink(
                $request->only('email')
            );

            return $this->success(null, 'If an account exists with that email, a reset link has been sent.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to send password reset link.', 500);
        }
    }

    /**
     * Reset password
     *
     * Reset the user's password using the token from the email link.
     * All existing API tokens will be revoked after reset.
     *
     * @unauthenticated
     *
     * @bodyParam token string required Reset token from the email. Example: abc123def456...
     * @bodyParam email string required Email address. Example: john@example.com
     * @bodyParam password string required New password (min 8 characters). Example: newpassword123
     * @bodyParam password_confirmation string required Must match password. Example: newpassword123
     *
     * @response 200 {"status":true,"message":"Password has been reset successfully.","data":null}
     * @response 422 {"status":false,"message":"This password reset token is invalid."}
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $passwordRules = $this->buildPasswordRules();

        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', ...$passwordRules],
        ]);

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password'       => $password,
                        'remember_token' => Str::random(60),
                    ])->save();

                    $user->tokens()->delete();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return $this->success(null, 'Password has been reset successfully.');
            }

            return $this->error(trans($status), 422);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to reset password.', 500);
        }
    }

    /**
     * Change password
     *
     * Update the authenticated user's password. Requires the current
     * password for verification. All other tokens are revoked after change.
     *
     * @authenticated
     *
     * @bodyParam current_password string required Current password. Example: oldpassword123
     * @bodyParam password string required New password (min 8 characters). Example: newpassword456
     * @bodyParam password_confirmation string required Must match new password. Example: newpassword456
     *
     * @response 200 {"status":true,"message":"Password changed successfully.","data":null}
     * @response 422 {"status":false,"message":"Current password is incorrect."}
     */
    public function changePassword(Request $request): JsonResponse
    {
        $passwordRules = $this->buildPasswordRules();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', ...$passwordRules],
        ]);

        try {
            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return $this->error('Current password is incorrect.', 422);
            }

            $user->update([
                'password' => $request->password,
            ]);

            $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

            return $this->success(null, 'Password changed successfully.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to change password.', 500);
        }
    }

    /**
     * Delete account
     *
     * Permanently delete the authenticated user's account and all associated data.
     * Requires password confirmation. This action cannot be undone.
     *
     * @authenticated
     *
     * @bodyParam password string required Password for confirmation. Example: password123
     *
     * @response 200 {"status":true,"message":"Account deleted successfully.","data":null}
     * @response 422 {"status":false,"message":"Password is incorrect."}
     * @response 422 {"status":false,"message":"Cannot delete account with active seller store. Please close your store first."}
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        try {
            $user = $request->user();

            if (!Hash::check($request->password, $user->password)) {
                return $this->error('Password is incorrect.', 422);
            }

            if ($user->seller && $user->seller->status === 'active') {
                return $this->error('Cannot delete account with active seller store. Please close your store first.', 422);
            }

            DB::transaction(function () use ($user) {
                $user->tokens()->delete();
                $user->profile?->delete();
                $user->delete();
            });

            return $this->success(null, 'Account deleted successfully.');
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to delete account.', 500);
        }
    }

    protected function token(User $user): array
    {
        $token = $user->createToken('api', ['*'], now()->addDays(30));

        return [
            'token' => $token->plainTextToken,
            'user'  => [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'email_verified' => $user->hasVerifiedEmail(),
            ],
        ];
    }

    protected function buildPasswordRules(): array
    {
        $sec = Setting::group('security');
        $minLen = (int) ($sec['password_min_length'] ?? 8);

        $rules = ["min:{$minLen}"];

        if (!empty($sec['password_require_uppercase'])) {
            $rules[] = 'regex:/[A-Z]/';
        }
        if (!empty($sec['password_require_number'])) {
            $rules[] = 'regex:/[0-9]/';
        }
        if (!empty($sec['password_require_symbol'])) {
            $rules[] = 'regex:/[^A-Za-z0-9]/';
        }

        return $rules;
    }
}
