<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register new user
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'is_active'=> true,
        ]);

        // Default buyer role
        $user->roles()->attach(
            Role::where('name', 'buyer')->first()
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Registration successful'
        ], 201);
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Account disabled'
            ], 403);
        }

        // Optional: revoke old tokens
        $user->tokens()->delete();

        $abilities = ['buyer'];

        if ($user->hasRole('admin')) {
            $abilities = ['admin'];
        } elseif ($user->is_seller) {
            $abilities = ['seller'];
        }

        $token = $user->createToken(
            'nextjs-token',
            $abilities,
            now()->addDays(7)
        )->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'user'  => $user,
                'token' => $token,
            ]
        ]);
    }


    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get logged in user
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('roles.permissions');

        return response()->json([
            'status' => 'success',
            'data'   => [
                'user' => $user
            ]
        ]);
    }

}
