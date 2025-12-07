<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

class SSOController extends Controller
{
    public function indexGoogle()
    {
        return Socialite::driver('google')->redirect();
    }


    public function storeGoogle()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::updateOrCreate([
                'email' => $googleUser->getEmail(),
            ], [
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
            ]);

            // Set a secure random password for OAuth users (they won't use it)
            if (empty($user->password)) {
                // Bypass the custom password setter for OAuth users
                $user->attributes['password'] = bcrypt(\Illuminate\Support\Str::random(32));
                $user->attributes['password_salt'] = null;
                $user->save();
            }

            Auth::login($user);

            return redirect('/dashboard');
        } catch (\Exception $e) {
            \Log::error('Google SSO Error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Google authentication failed. Please try again.');
        }
    }

    public function indexFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function storeFacebook()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();

            $user = User::updateOrCreate([
                'email' => $facebookUser->getEmail(),
            ], [
                'name' => $facebookUser->getName(),
                'facebook_id' => $facebookUser->getId(),
                'facebook_token' => $facebookUser->token,
            ]);

            // Set a secure random password for OAuth users (they won't use it)
            if (empty($user->password)) {
                // Bypass the custom password setter for OAuth users
                $user->attributes['password'] = bcrypt(\Illuminate\Support\Str::random(32));
                $user->attributes['password_salt'] = null;
                $user->save();
            }

            Auth::login($user);

            return redirect('/dashboard');
        } catch (\Exception $e) {
            \Log::error('Facebook SSO Error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Facebook authentication failed. Please try again.');
        }
    }

}
