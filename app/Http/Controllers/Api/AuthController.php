<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

/**
 * @group Authentication
 *
 * APIs for user authentication using Laravel Sanctum.
 */
class AuthController extends Controller
{
    /**
     * Register user
     *
     * Create a new user account.
     *
     * @bodyParam name string required Full name. Example: John Doe
     * @bodyParam email string required Email address. Example: john@example.com
     * @bodyParam password string required Password (min 8 chars).
     *
     * @response 201 {
     *  "status": true,
     *  "message": "Registration successful"
     * }
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'is_active'  => true,
            'is_verified'=> false,
        ]);

        // ✅ Assign default role: customer
        $customerRole = Role::where('name', 'customer')->first();

        if ($customerRole) {
            $user->roles()->syncWithoutDetaching([$customerRole->id]);
        }

        return $this->successResponse($this->token($user), 'Registration successful', 201);
    }

    /**
     * Login user
     *
     * Authenticate user and return API token.
     *
     * @bodyParam email string required Email. Example: john@example.com
     * @bodyParam password string required Password.
     *
     * @response 200 {
     *  "status": true,
     *  "token": "..."
     * }
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        if (!$user->is_active) {
            return $this->errorResponse('Account is disabled', 403);
        }

        return $this->successResponse($this->token($user), 'Login successful');
    }

    /**
     * Logout user
     *
     * Revoke current access token.
     *
     * @authenticated
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    protected function token(User $user): array
    {
        return [
            'token' => $user->createToken('api')->plainTextToken,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ];
    }

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
