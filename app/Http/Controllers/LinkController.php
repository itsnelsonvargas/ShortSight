<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\Visitor;
use Illuminate\Support\Str;
use App\Services\UrlSafetyService;
use App\Services\RedisCacheService;
use App\Helpers\AnalyticsHelper; 


class LinkController extends Controller
{
    public function index()
    {
        $newLinks = Link::latest()->take(5)->get();
        $newLinks = $newLinks->toArray();
        $data = [
            'newLinks'      => $newLinks,
           
            'submittedUrl'  => '',
        ];
 
        // Code to show form to create a new link
        return view('welcome', compact('data'));
    }

    public function store(Request $request)
    {
        // Code to save a new link
    }

    
    public function storeWithoutUserAccount(Request $request)
    {   
        /*
        *
        * storeWithoutUserAccount(Request $request)
        * 1. Validate the URL and custom slug input
        *
        */
        $request->validate([
            'url'               => 'required|url',
            'customSlugInput'   => 'nullable|alpha_dash|max:20',
        ]);

        $link = new Link();
        $link->url = $request->url;

        /***********************************************
        *                                              *
        * 2. Check a custom slug if valid.             *
        *                                              *
        ************************************************/

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

        /****************************************************************
        *                                                               *
        * Check if the URL is malicious using Google Safe Browsing API  *
        *                                                               *
        ****************************************************************/

        $cacheService = app(RedisCacheService::class);

        // Check cached URL safety first
        $isSafe = $cacheService->getCachedUrlSafety($request->url);

        if ($isSafe === null) {
            // Not in cache, check with service
            $urlSafetyService = new UrlSafetyService();
            $isMalicious = $urlSafetyService->isMalicious($request->url);

            // Cache the result
            $cacheService->cacheUrlSafety($request->url, !$isMalicious);
            $isSafe = !$isMalicious;
        }

        if (!$isSafe) {
            return back()->withErrors(['url' => 'The URL is malicious.']);
        }

      
        $link->slug = $slug;
        $link->save();


        /**
         * Return the SLUG and URL
         */
        $data = [
            'newSlug'       => $slug,
            'submittedUrl'  => $request->url,
            'short_url'     => url($slug),
        ];

        // Return JSON for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'slug' => $slug,
                'short_url' => url($slug),
                'original_url' => $request->url,
            ]);
        }

        return view('welcome', compact('data'));
    }
    
    public function downloadPng($slug)
    {
        $png = QrCode::format('png')
                ->size(200)
                ->generate($slug);

        return response($png, 200)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'attachment; filename="qrcode.png"');
    }

    public function show($slug)
    {
        try {
            $cacheService = app(RedisCacheService::class);

            // Try to get link from cache first
            $link = $cacheService->getCachedSlugLookup($slug);

            if (!$link) {
                // If not in cache, get from database
                $link = Link::where('slug', $slug)->firstOrFail();

                // Cache the result for future requests
                $cacheService->cacheSlugLookup($slug, $link);
                $cacheService->cacheLinkMetadata($link);
            }

            // Track visitor analytics asynchronously
            $this->trackVisitorAnalytics($slug, request());

            // Increment click count in cache
            $cacheService->incrementClickCount($slug);

            //redirect to the URL based on the slug
            return redirect($link->url);
        } catch (\Exception $e) {
            //if no link is found, it will throw a 404 error
            return abort(404);
        }
    }

    /**
     * Track visitor analytics for link clicks
     *
     * @param string $slug
     * @param Request $request
     * @return void
     */
    private function trackVisitorAnalytics(string $slug, Request $request): void
    {
        try {
            // Get visitor information
            $visitorInfo = getVisitorInfo($request);

            // Create visitor record asynchronously to avoid slowing down redirects
            Visitor::create([
                'slug' => $slug,
                'ip_address' => $visitorInfo['ip_address'],
                'user_agent' => $visitorInfo['user_agent'],
                'browser' => $visitorInfo['browser'],
                'device' => $visitorInfo['device'],
                'platform' => $visitorInfo['platform'],
                'referer' => $visitorInfo['referer'],
                'country' => $visitorInfo['location']['country'],
                'city' => $visitorInfo['location']['city'],
                'region' => $visitorInfo['location']['region'],
                'postal_code' => $visitorInfo['location']['postal_code'],
                'latitude' => $visitorInfo['location']['latitude'],
                'longitude' => $visitorInfo['location']['longitude'],
            ]);
        } catch (\Exception $e) {
            // Log analytics error but don't fail the redirect
            \Log::error('Failed to track visitor analytics', [
                'slug' => $slug,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function checkSlug(Request $request)
    {
        //Get the slug to validate
        $slug = $request->input('slug');

        //Check if the slug is already used
        $exists = \App\Models\Link::where('slug', $slug)->exists();

        return response()->json(['available' => !$exists]);
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
