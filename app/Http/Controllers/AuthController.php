<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function logout(Request $request)
    {
        Auth::logout(); // Logs out the user

        // Invalidate session and regenerate CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
        return redirect('/'); // Redirect to home or login page
    }

    /*************************************
    *                                    *
    * Not yet connect to logging in.     *
    *                                    *
    *************************************/
    public function login(Request $request)
    {
        // Find user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and password is correct
        if (! $user || ! $user->verifyPassword($request->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Rehash password if needed (for security upgrades)
        $user->rehashPasswordIfNeeded($request->password);

        // Create a new personal access token with optional abilities (e.g., read/write)
        $token = $user->createToken('api-token', ['read', 'write'])->plainTextToken;

        // Return the token in the response
        return response()->json(['token' => $token]);
    }


}
