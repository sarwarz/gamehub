<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * Creates a new buyer account for the eCommerce platform.
     *
     * @group Authentication
     *
     * @bodyParam name string required Full name of the user. Example: John Doe
     * @bodyParam email string required Email address (must be unique). Example: john@example.com
     * @bodyParam password string required Minimum 8 characters. Example: password123
     * @bodyParam password_confirmation string required Must match password. Example: password123
     *
     * @response 201 {
     *   "status": "success",
     *   "message": "Registration successful"
     * }
     *
     * @response 422 {
     *   "message": "The email has already been taken.",
     *   "errors": {
     *     "email": ["The email has already been taken."]
     *   }
     * }
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        // Assign default customer role
        $user->roles()->attach(
            Role::where('name', 'customer')->first()
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Registration successful'
        ], 201);
    }

    /**
     * Login user
     *
     * Authenticates a user and returns a Sanctum token.
     *
     * @group Authentication
     *
     * @bodyParam email string required Registered email address. Example: john@example.com
     * @bodyParam password string required User password. Example: password123
     *
     * @response 200 {
     *   "status": "success",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com"
     *     },
     *     "token": "1|xxxxxxxxxxxxxxxxxxxxxxxx"
     *   }
     * }
     *
     * @response 401 {
     *   "status": "error",
     *   "message": "Invalid credentials"
     * }
     *
     * @response 403 {
     *   "status": "error",
     *   "message": "Account disabled"
     * }
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

        // Revoke previous tokens
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
     * Logout user
     *
     * Revokes the current access token.
     *
     * @group Authentication
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Logged out successfully"
     * }
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
     * Get authenticated user
     *
     * Returns the currently logged-in user with roles and permissions.
     *
     * @group Authentication
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "success",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "roles": [],
     *       "permissions": []
     *     }
     *   }
     * }
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
