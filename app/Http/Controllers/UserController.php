<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function create()
    {
        // Code to show form to create a new User
    }

    public function store(Request $request)
    {
        // Code to save a new User
        return view('user.registration');
    }
 
}
