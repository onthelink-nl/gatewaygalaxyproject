<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Login and return a token and user details in a JSON response.
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        // Validate the incoming request which is a JSON object
        $email = $request->json('email') ?? null;
        $password = $request->json('password') ?? null;
        if (is_null($email) || is_null($password)) {
            throw ValidationException::withMessages([
                'email' => 'The email field is required.',
                'password' => 'The password field is required.',
            ]);
        }

        // Attempt to authenticate
        if (!auth()->attempt(['email' => $email, 'password' => $password])) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Retrieve the authenticated user
        $user = auth()->user();

        // Ensure the authenticated user is a valid instance of the User model
        if (!$user instanceof User) {
            throw new \LogicException('Authenticated user is not an instance of the expected User model.');
        }

        // Generate a new token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return the token and user details
        return response()->json([
            'token' => $token,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'address' => $user->address,
            ],
        ]);
    }

    /**
     * Logout the authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        // Ensure the request is authenticated
        if (!$request->user()) {
            return response()->json([
                'message' => 'No authenticated user found.',
            ], 401);
        }

        // Revoke the current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Logout the authenticated user from all sessions.
     */
    public function logoutFromAllSessions(Request $request): JsonResponse
    {
        // Ensure the request is authenticated
        if (!$request->user()) {
            return response()->json([
                'message' => 'No authenticated user found.',
            ], 401);
        }

        // Revoke all tokens for the user
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all sessions successfully',
        ]);
    }
}
