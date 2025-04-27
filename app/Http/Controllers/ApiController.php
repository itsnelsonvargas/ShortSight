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
}
