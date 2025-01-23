<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Update the authenticated user's information.
     * @throws ValidationException
     */
    public function updateUserInfo(Request $request): JsonResponse
    {
        // Get the authenticated user (we know it exists because of auth:sanctum middleware)
        $user = $request->user();

        // Validate the incoming JSON request
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'address' => 'sometimes|string|max:255',
        ]);

        // Update only the validated fields
        $user->update($validatedData);

        return response()->json([
            'message' => 'User info updated successfully',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'address' => $user->address,
            ],
        ]);
    }
}
