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


    /********************************************
    *                                           *                                                         
    * This is for deleting a token for the user *
    *                                           *
    *********************************************/
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


    /***********************************************
     *                                             *
     * get the store link using the slug provided  *
     *                                             *
     **********************************************/
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


    /***********************************************
     *                                             *
     * Check if the slug is available              *
     *               true or false                 *                              
     *                                             *
     **********************************************/
    public function isSlugAvailable($slug)
    {
        // Check if the slug is available
        $link = Link::where('slug', $slug)->first();

        if ($link) {
            return response()->json(['available' => false]);
        }

        return response()->json(['available' => true]);
    }


    /***********************************************
     *                                             *
     * Check if the link is in the database        *
     *              true or false                  *
     *                                             *
     **********************************************/
    public function isLinkInDatabase($link)
    {
        // Check if the link is in the database
        $link = Link::where('url', $link)->first();

        if ($link) {
            return response()->json(['in_database' => true]);
        }

        return response()->json(['in_database' => false]);
    }


    /***********************************************
     *                                             *
     * get the slug of the link provided           *
     *                                             *
     **********************************************/

    public function getSlugOfLink($link)
    {
        // Check if the link is in the database
        $link = Link::where('url', $link)->first();

        if ($link) {
            return response()->json([
                'in_database' => false,
                'slug'        => $link->slug
            ]);
        }

        return response()->json(['in_database' => false]);
    }



    /******************************************
     *                                        *
     * Check if the link is safe              *
     *                                        *
     *****************************************/
    public function isUrlSafe(Request $request)
    {
        // Validate the request
        $request->validate([
            'link' => 'required|url',
        ]);

        $link = $request->input('link');

        // Check if the link is safe
 
        if(  (new UrlSafetyService())->isMalicious($link) ) {
            return response()->json(['safe' => false]);
        }
        return response()->json(['safe' => true]);
    }



}
