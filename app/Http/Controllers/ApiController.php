<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function getStoredLink($slug)
    {

        // Find the link by slug
        $link = Link::where('slug', $slug)->first();

        if (!$link) {
            return response()->json(['error' => 'Link not found'], 404);
        }

        // Return the link details
        return response()->json($link);
    }
}
