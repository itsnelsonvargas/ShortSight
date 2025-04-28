<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\Models\Link;

class ApiController extends Controller
{
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
}
