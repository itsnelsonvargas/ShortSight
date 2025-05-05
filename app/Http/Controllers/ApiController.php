<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\Models\Link;
use App\Services\UrlSafetyService;  
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ApiController extends Controller
{   

    /********************************************
    *                                           *
    * This is for creating a token for the user *
    *                                           *
    ********************************************/

    public function createToken(Request $request)
    {
        // Validate the request
        $request->validate([
            'email'     => 'required|email',
            'password'  => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Invalid credentials'
            ], 401);
        }

        // Generate a new token
        $token = $user->createToken('API Token')->plainTextToken;

        // Return the token (and optionally the user info)
        return response()->json([
            'access_token'  => $token,   
        ], 200);
    }



    public function deleteToken(Request $request)
    {
        // Validate the request
        $request->validate([
            'token' => 'required',
        ]);

        // Find the user by token
        $user = User::where('id', $request->user()->id)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Revoke the token
        $user->tokens()->where('id', $request->token)->delete();

        return response()->json(['message' => 'Token revoked successfully'], 200);
    }



    public function getStoredLink($slug)
    {

        // Find the link by slug
        $link = Link::where('slug', $slug)->first();

        if (!$link) {
            return response()->json(['error' => 'Link not found'], 404);
        }

        $data = [
            'link'   => $link->url,
        ];

        // Return the link details
        return response()->json($data);
    }



    public function isSlugAvailable($slug)
    {
        // Check if the slug is available
        $link = Link::where('slug', $slug)->first();

        if ($link) {
            return response()->json(['available' => false]);
        }

        return response()->json(['available' => true]);
    }


    
    public function isLinkInDatabase($link)
    {
        // Check if the link is in the database
        $link = Link::where('url', $link)->first();

        if ($link) {
            return response()->json(['in_database' => true]);
        }

        return response()->json(['in_database' => false]);
    }



    public function getSlugOfLink($link)
    {
        // Check if the link is in the database
        $link = Link::where('url', $link)->first();

        if ($link) {
            return response()->json([
                'in_database' => false,
                'slug' => $link->slug
            ]);
        }

        return response()->json(['in_database' => false]);
    }



    public function isUrlSafe($link)
    {
        if(  (new UrlSafetyService())->isMalicious($link) ) {
            return response()->json(['safe' => false]);
        }

        return response()->json(['safe' => true]);
    }



}
