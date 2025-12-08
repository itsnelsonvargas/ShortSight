<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return view('user.registration');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password, // This will use the custom password setter
            ]);

            // Send email verification notification
            $user->sendEmailVerificationNotification();

            return response()->json([
                'message' => 'User registered successfully. Please check your email to verify your account.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'requires_verification' => true
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user account (GDPR right to erasure)
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        // Validate current password for security
        $request->validate([
            'password' => 'required|string',
        ]);

        // Verify password
        if (!$user->verifyPassword($request->password)) {
            return response()->json([
                'message' => 'Invalid password. Account deletion cancelled.'
            ], 403);
        }

        try {
            // Get all slugs for user's links before deleting them
            $userSlugs = $user->links()->pluck('slug')->toArray();

            // Delete all user's links
            $user->links()->delete();

            // Delete all visitor analytics for user's links
            // Note: This might be a lot of data, consider soft deletes or batch processing
            if (!empty($userSlugs)) {
                \App\Models\Visitor::whereIn('slug', $userSlugs)->delete();
            }

            // Delete the user account
            $user->delete();

            return response()->json([
                'message' => 'Your account and all associated data have been permanently deleted.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Account deletion failed. Please contact support.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
