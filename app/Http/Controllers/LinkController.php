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

    
    public function storeWithoutUserAccount(Request $request)
    {

        // Create a new Link model instance
        $link = new Link();
        
        // Save the URL passed in the request to the Link model
        $link->url = $request->url;
    
        // Generate a random 7-character slug using md5 and rand()
        $randomSlug = substr(md5(rand()), 0, 7); // Generate a random slug of 7 characters
    
        // Assign the generated slug to the 'short' attribute of the Link model
        $link->short = $randomSlug;
    
        // Save the new Link model instance to the database
        $link->save();
    
        // Return the 'welcome' view with the new random slug
        return view('welcome', ['newSlug' => $randomSlug, 'submittedUrl' => $request->url]);
    }
    

    public function show($id)
    {
        try {
         
            //Look for the slug in the database
            $link = Link::where('slug', $slug)->firstOrFail();
            
            
            //redirect to the URL based on the slug  (Can be viewed in the database)
            return redirect($link->url); 
        } catch (\Exception $e) {
            //if no link is found, it will throw a 404 error
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
