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
        $googleUser = Socialite::driver('google')->stateless()->user();  

        $user = User::updateOrCreate([
            'email'                  => $googleUser->getEmail(),
        ], [
            'name'                  => $googleUser->getName(),
            'google_id'             => $googleUser->getId(),
        ]);

        // Set a secure random password for OAuth users (they won't use it)
        if (empty($user->password_salt)) {
            $user->password = \Illuminate\Support\Str::random(32);
            $user->save();
        }
    
        Auth::login($user); 
    
        return view('welcome');
    }

    public function indexFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function storeFacebook()
    {
        $facebookUser = Socialite::driver('facebook')->stateless()->user();

        $user = User::updateOrCreate(
            ['facebook_id'          => $facebookUser->getId()],
            [
                'name'              => $facebookUser->getName(),
                'email'             => $facebookUser->getEmail(),
                'facebook_token'    => $facebookUser->token,
            ]
        );

        // Set a secure random password for OAuth users (they won't use it)
        if (empty($user->password_salt)) {
            $user->password = \Illuminate\Support\Str::random(32);
            $user->save();
        }
    
        Auth::login($user);
    
        return redirect('/dashboard');  
    }

}
