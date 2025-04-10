<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

class SSOController extends Controller
{
    public function index()
    {
        return Socialite::driver('google')->redirect();
    }


    public function store()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();  

        $user = User::updateOrCreate([
            'email' => $googleUser->getEmail(),
        ], [
            'name' => $googleUser->getName(),
            'password' => bcrypt('random_password'),
            'google_id' => $googleUser->getId(),
        ]);
    
        Auth::login($user); 
    
        return view('welcome');
    }


}
