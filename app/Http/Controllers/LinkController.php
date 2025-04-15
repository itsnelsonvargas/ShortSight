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
        return view('welcome');
    }

    public function store(Request $request)
    {
        // Code to save a new link
    }

    
    public function storeWithoutUserAccount(Request $request)
    {
       // dd($request->all());
         
        // Create a new Link model instance
        $link = new Link();
        
        // Save the URL passed in the request to the Link model
        $link->url = $request->url;
    
        $slug ="";
        if(isset($request->customSlug)){ 
            //if User select custom slug
            $slug = $request->customSlugInput;
        }
        else
        {
         
            // Generate a random 7-character slug using md5 and rand()
            $randomSlug = substr(md5(rand()), 0, 7); // Generate a random slug of 7 characters
        
            $randomSlugB = substr(md5(rand()), 0, 3) . '-' . substr(md5(rand()), 3, 3); // Generate a random slug of 7 characters with a hyphen
       
            // Check if the generated slug already exists in the database
            while (Link::where('slug', $randomSlug)->exists()) {
                // If the generated slug already exists in the database, generate a new one
                $randomSlug = substr(md5(rand()), 0, 7); //
            }

            $slug = $randomSlug;
        }
   
       
        
        

        // Assign the generated slug to the 'short' attribute of the Link model
        $link->slug = $slug;
    
        // Save the new Link model instance to the database
        $link->save();

        $data = [
            'newSlug' => $slug,
            'submittedUrl' => $request->url,
            'qrCode'   => '',
        ];  
    
        // Return the 'welcome' view with the new random slug
        return view('welcome', compact('data'));
    }
    
    public function downloadPng($slug)
    {
        $png = QrCode::format('png')->size(200)->generate($slug);

        return response($png, 200)
        ->header('Content-Type', 'image/png')
        ->header('Content-Disposition', 'attachment; filename="qrcode.png"');
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

    public function checkSlug(Request $request)
    {    
        //Get the slug to validate
        $slug = $request->input('slug');
       

        //Check if the slug is already used
        $exists = \App\Models\Link::where('slug', $slug)->exists();  
       
        //dd(  $exists) ;
        return response()->json(['exists' => $exists]);
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

    public function test()
    {
        return view('test');
    }


    public function testQR()
    {
 
        $data = 'https://facebook.com';
        return view('generateQR', compact('data'));
 
    }
}
