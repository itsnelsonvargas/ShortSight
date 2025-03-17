<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;

class LinkController extends Controller
{
    public function index()
    {
        // Code to list all links
    }

    public function create()
    {
        // Code to show form to create a new link
    }

    public function store(Request $request)
    {
        // Code to save a new link
    }

    public function show($id)
    {
        try {
            $link = Link::where('short', $id)->firstOrFail();
            //if no link is found, it will throw a 404 error
            
            return redirect($link->url);
        } catch (\Exception $e) {
            return abort(404);
        }
          
    }

    public function edit($id)
    {
        // Code to show form to edit a specific link
    }

    public function update(Request $request, $id)
    {
        // Code to update a specific link
    }

    public function destroy($id)
    {
        // Code to delete a specific link
    }
}
