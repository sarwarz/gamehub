<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Setting;
use App\Models\UserProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $securitySettings = Setting::group('security');

        if (!empty($securitySettings['ip_whitelist_enabled'])) {
            $whitelist = $securitySettings['ip_whitelist'] ?? [];
            if (is_string($whitelist)) {
                $whitelist = array_filter(array_map('trim', explode("\n", $whitelist)));
            }
            if (!empty($whitelist) && !in_array($request->ip(), $whitelist)) {
                return back()->withErrors([
                    'email' => 'Access denied. Your IP address is not allowed.',
                ])->onlyInput('email');
            }
        }

        $request->authenticate();

        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isInternal()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'You do not have access to the admin panel.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['last_login_at' => now(), 'last_login_ip' => $request->ip()]
        );

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
