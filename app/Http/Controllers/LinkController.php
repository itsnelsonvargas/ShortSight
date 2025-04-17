<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;
use Illuminate\Support\Str;


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
        $request->validate([
            'url' => 'required|url',
            'customSlugInput' => 'nullable|alpha_dash|max:20',
        ]);

        $link = new Link();
        $link->url = $request->url;

        if ($request->has('customSlug')) {
            $slug = $request->customSlugInput;

            if (Link::where('slug', $slug)->exists()) {
                return back()->withErrors(['customSlugInput' => 'This custom slug is already taken.']);
            }
        } else {
            do {
                $slug = Str::random(7);
            } while (Link::where('slug', $slug)->exists());
        }

        $link->slug = $slug;
        $link->save();

        $data = [
            'newSlug' => $slug,
            'submittedUrl' => $request->url,
            'qrCode' => '', // Optional: add QR generation here
        ];

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
