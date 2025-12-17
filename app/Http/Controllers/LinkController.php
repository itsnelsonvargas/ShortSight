<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;
use Illuminate\Support\Str;
use App\Services\UrlSafetyService;
use App\Services\RedisCacheService;
use App\Services\RecaptchaService;
use App\Helpers\AnalyticsHelper; 
use App\Jobs\ProcessVisitorAnalytics;


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
        try {
            /*
            * storeWithoutUserAccount(Request $request)
            * 1. Validate the URL and custom slug input
            */
            $validated = $request->validate([
                'url'               => 'required|url|max:2048',
                'customSlugInput'   => 'nullable|alpha_dash|max:20|min:3',
                'recaptcha_token'   => 'nullable|string',
                'password'          => 'nullable|string|min:4|max:255',
            ]);

            /***********************************************
            *                                              *
            * 2. Validate reCAPTCHA for spam prevention    *
            *                                              *
            ************************************************/

            $recaptchaService = app(RecaptchaService::class);
            if ($recaptchaService->isEnabled()) {
                $recaptchaToken = $request->input('recaptcha_token');

                if (!$recaptchaToken) {
                    $error = 'reCAPTCHA verification is required to prevent spam.';
                    return $this->handleValidationError($request, ['recaptcha_token' => $error]);
                }

                $recaptchaResult = $recaptchaService->validateToken($recaptchaToken, 'shorten_url');

                if (!$recaptchaResult['success']) {
                    $error = 'reCAPTCHA verification failed. Please try again.';
                    return $this->handleValidationError($request, ['recaptcha_token' => $error]);
                }
            }

            /***********************************************
            *                                              *
            * 3. Check a custom slug if valid.             *
            *                                              *
            ************************************************/

            $slug = null;
            if ($request->has('customSlug') && !empty($request->customSlugInput)) {
                $slug = trim($request->customSlugInput);

                // Check if slug is available
                if (Link::where('slug', $slug)->exists()) {
                    $message = 'The custom slug "' . $slug . '" is already taken. Please choose a different one.';
                    return $this->handleValidationError($request, ['customSlugInput' => $message]);
                }

                // Check for reserved words
                if (in_array(strtolower($slug), ['admin', 'api', 'www', 'dashboard', 'login', 'register'])) {
                    $message = 'This slug is reserved and cannot be used.';
                    return $this->handleValidationError($request, ['customSlugInput' => $message]);
                }
            } else {
                // Generate unique slug with retry limit
                $attempts = 0;
                $maxAttempts = 10;

                do {
                    if ($attempts >= $maxAttempts) {
                        $error = 'Unable to generate a unique slug. Please try with a custom slug.';
                        return $this->handleError($request, $error, 'slug_generation_failed');
                    }

                    $slug = Str::random(7);
                    $attempts++;
                } while (Link::where('slug', $slug)->exists());
            }

            /****************************************************************
            *                                                               *
            * Comprehensive URL validation including security checks        *
            *                                                               *
            ****************************************************************/

            $cacheService = app(RedisCacheService::class);
            $urlSafetyService = app(UrlSafetyService::class);

            // Check cached comprehensive validation first
            $validationResult = $cacheService->getCachedUrlValidation($request->url);

            if ($validationResult === null) {
                // Not in cache, perform comprehensive validation
                try {
                    $validationResult = $urlSafetyService->validateUrl($request->url);

                    // Cache the comprehensive validation result
                    $cacheService->cacheUrlValidation($request->url, $validationResult);
                } catch (\Exception $e) {
                    \Log::error('URL validation failed', [
                        'url' => $request->url,
                        'error' => $e->getMessage()
                    ]);

                    $error = 'Unable to validate URL at this time. Please try again later.';
                    return $this->handleError($request, $error, 'validation_service_unavailable');
                }
            }

            // Check if URL passed all validation checks
            if (!$validationResult['is_safe']) {
                $errorMessage = 'URL validation failed: ' . implode(', ', $validationResult['errors']);
                return $this->handleValidationError($request, ['url' => $errorMessage]);
            }

            // Create the link
            try {
                $link = new Link();
                $link->url = $request->url;
                $link->slug = $slug;

                // Handle password protection (premium feature)
                if ($request->filled('password')) {
                    // Check if user is authenticated (premium feature)
                    if (!auth()->check()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Password protection requires a premium account. Please upgrade to use this feature.',
                            'type' => 'premium_required'
                        ], 403);
                    }

                    // Set password for the link
                    $link->setPassword($request->password);
                }

                $link->save();
            } catch (\Exception $e) {
                \Log::error('Failed to save link', [
                    'url' => $request->url,
                    'slug' => $slug,
                    'error' => $e->getMessage()
                ]);

                $error = 'Unable to create your short link. Please try again.';
                return $this->handleError($request, $error, 'database_save_failed');
            }

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

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->handleValidationError($request, $e->errors());
        } catch (\Exception $e) {
            \Log::error('Unexpected error in link creation', [
                'url' => $request->url ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $error = 'An unexpected error occurred. Please try again later.';
            return $this->handleError($request, $error, 'unexpected_error');
        }
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
                $link = Link::where('slug', $slug)->first();

                if (!$link) {
                    \Log::info('Link not found', ['slug' => $slug, 'ip' => request()->ip()]);
                    return abort(404);
                }

                // Cache the result for future requests
                try {
                    $cacheService->cacheSlugLookup($slug, $link);
                    $cacheService->cacheLinkMetadata($link);
                } catch (\Exception $e) {
                    // Log cache error but continue with redirect
                    \Log::warning('Failed to cache link data', [
                        'slug' => $slug,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Check if link is disabled
            if ($link->is_disabled) {
                return response()->view('errors.link-error', [
                    'message' => 'This link has been disabled by the owner.',
                    'title' => 'Link Disabled',
                    'slug' => $slug
                ], 410);
            }

            // Check if link has expired
            if ($link->isExpired()) {
                // If auto-delete is enabled, delete the link
                if ($link->shouldAutoDelete()) {
                    $link->delete();
                }

                return response()->view('errors.link-error', [
                    'message' => 'This link has expired and is no longer available.',
                    'title' => 'Link Expired',
                    'slug' => $slug
                ], 410);
            }

            // Check if link is password protected
            if ($link->is_password_protected) {
                // Check if password was already verified in this session
                $sessionKey = "link_password_verified_{$slug}";
                $verified = session($sessionKey, false);

                if (!$verified) {
                    // Show password prompt page
                    return view('password-prompt', [
                        'slug' => $slug,
                        'title' => $link->title ?: 'Password Protected Link',
                        'description' => $link->description ?: 'This link requires a password to access.'
                    ]);
                }
            }

            // Validate the destination URL
            if (empty($link->url) || !filter_var($link->url, FILTER_VALIDATE_URL)) {
                \Log::error('Invalid destination URL for slug', [
                    'slug' => $slug,
                    'url' => $link->url,
                    'ip' => request()->ip()
                ]);

                return response()->view('errors.link-error', [
                    'message' => 'This link appears to be corrupted and cannot be accessed.',
                    'title' => 'Invalid Link',
                    'slug' => $slug
                ], 500);
            }

            // Track visitor analytics asynchronously (don't fail if analytics fail)
            try {
                $this->trackVisitorAnalytics($slug, request());
            } catch (\Exception $e) {
                \Log::warning('Analytics tracking failed', [
                    'slug' => $slug,
                    'error' => $e->getMessage()
                ]);
            }

            // Increment click count in cache (don't fail if caching fails)
            try {
                $cacheService->incrementClickCount($slug);
            } catch (\Exception $e) {
                \Log::warning('Click count increment failed', [
                    'slug' => $slug,
                    'error' => $e->getMessage()
                ]);
            }

            // Perform the redirect
            return redirect($link->url, 302);

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error during link lookup', [
                'slug' => $slug,
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);

            return response()->view('errors.link-error', [
                'message' => 'Service temporarily unavailable. Please try again later.',
                'title' => 'Service Unavailable',
                'slug' => $slug
            ], 503);

        } catch (\Exception $e) {
            \Log::error('Unexpected error during link redirect', [
                'slug' => $slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => request()->ip()
            ]);

            return response()->view('errors.link-error', [
                'message' => 'An unexpected error occurred. Please try again later.',
                'title' => 'Link Error',
                'slug' => $slug
            ], 500);
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
        // Capture a lightweight snapshot to pass into the queue job.
        $visitorSnapshot = [
            'ip_address' => $request->ip() === '127.0.0.1' ? '103.192.185.61' : $request->ip(),
            'user_agent' => $request->header('user-agent'),
            'referer' => $request->header('referer'),
        ];

        // Dispatch async analytics processing so redirects stay fast.
        ProcessVisitorAnalytics::dispatch($slug, $visitorSnapshot);
    }

    public function checkSlug(Request $request, $slug = null)
    {
        // Get the slug to validate (either from parameter or query)
        $slugToCheck = $slug ?? $request->input('slug');

        if (!$slugToCheck) {
            return response()->json(['available' => false, 'message' => 'Slug parameter required']);
        }

        // Check if the slug is already used
        $exists = \App\Models\Link::where('slug', $slugToCheck)->exists();

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

    /**
     * Get authenticated user's links
     */
    public function getUserLinks(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $links = Link::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->with(['visitors' => function($query) {
                    $query->selectRaw('link_id, COUNT(*) as click_count')
                          ->groupBy('link_id');
                }])
                ->paginate(20);

            // Transform the data for frontend consumption
            $links->getCollection()->transform(function ($link) {
                    return [
                        'id' => $link->id,
                        'slug' => $link->slug,
                        'url' => $link->url,
                        'short_url' => url($link->slug),
                        'is_password_protected' => $link->is_password_protected,
                        'is_disabled' => $link->is_disabled,
                        'expires_at' => $link->expires_at,
                        'auto_delete_expired' => $link->auto_delete_expired,
                        'is_expired' => $link->isExpired(),
                        'clicks' => $link->visitors->first()->click_count ?? 0,
                        'created_at' => $link->created_at,
                        'updated_at' => $link->updated_at,
                    ];
            });

            return response()->json([
                'links' => $links,
                'total' => $links->total(),
                'per_page' => $links->perPage(),
                'current_page' => $links->currentPage(),
                'last_page' => $links->lastPage(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to get user links', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to retrieve links'
            ], 500);
        }
    }

    /**
     * Create authenticated user's link
     */
    public function storeAuthenticated(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $validated = $request->validate([
                'url' => 'required|url|max:2048',
                'customSlugInput' => 'nullable|alpha_dash|max:20|min:3',
                'password' => 'nullable|string|min:4|max:255',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:500',
                'expires_at' => 'nullable|date|after:now',
                'auto_delete_expired' => 'boolean',
            ]);

            $slug = null;
            if ($request->has('customSlug') && !empty($request->customSlugInput)) {
                $slug = trim($request->customSlugInput);

                // Check if slug is available
                if (Link::where('slug', $slug)->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The custom slug "' . $slug . '" is already taken. Please choose a different one.',
                        'type' => 'validation_error'
                    ], 422);
                }

                // Check for reserved words
                if (in_array(strtolower($slug), ['admin', 'api', 'www', 'dashboard', 'login', 'register'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This slug is reserved and cannot be used.',
                        'type' => 'validation_error'
                    ], 422);
                }
            } else {
                // Generate unique slug
                $attempts = 0;
                $maxAttempts = 10;

                do {
                    if ($attempts >= $maxAttempts) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Unable to generate a unique slug. Please try with a custom slug.',
                            'type' => 'slug_generation_failed'
                        ], 500);
                    }

                    $slug = Str::random(7);
                    $attempts++;
                } while (Link::where('slug', $slug)->exists());
            }

            // URL validation
            $cacheService = app(RedisCacheService::class);
            $urlSafetyService = app(UrlSafetyService::class);

            $validationResult = $cacheService->getCachedUrlValidation($request->url);

            if ($validationResult === null) {
                try {
                    $validationResult = $urlSafetyService->validateUrl($request->url);
                    $cacheService->cacheUrlValidation($request->url, $validationResult);
                } catch (\Exception $e) {
                    \Log::error('URL validation failed', [
                        'url' => $request->url,
                        'error' => $e->getMessage()
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Unable to validate URL at this time. Please try again later.',
                        'type' => 'validation_service_unavailable'
                    ], 500);
                }
            }

            if (!$validationResult['is_safe']) {
                $errorMessage = 'URL validation failed: ' . implode(', ', $validationResult['errors']);
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'type' => 'validation_error'
                ], 422);
            }

            // Create the link
            $link = new Link();
            $link->url = $request->url;
            $link->slug = $slug;
            $link->user_id = $user->id;
            $link->title = $request->title;
            $link->description = $request->description;
            $link->expires_at = $request->expires_at;
            $link->auto_delete_expired = $request->boolean('auto_delete_expired', false);

            // Handle password protection
            if ($request->filled('password')) {
                $link->setPassword($request->password);
            }

            $link->save();

            return response()->json([
                'success' => true,
                'link' => [
                    'id' => $link->id,
                    'slug' => $slug,
                    'short_url' => url($slug),
                    'original_url' => $request->url,
                    'is_password_protected' => $link->is_password_protected,
                    'title' => $link->title,
                    'description' => $link->description,
                    'expires_at' => $link->expires_at,
                    'auto_delete_expired' => $link->auto_delete_expired,
                    'is_expired' => $link->isExpired(),
                    'created_at' => $link->created_at,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'type' => 'validation_error'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to create authenticated link', [
                'user_id' => auth()->id(),
                'url' => $request->url ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create your short link. Please try again.',
                'type' => 'database_save_failed'
            ], 500);
        }
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

    /**
     * Handle validation errors consistently
     */
    protected function handleValidationError(Request $request, array $errors)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors,
                'type' => 'validation_error'
            ], 422);
        }

        return back()->withErrors($errors)->withInput();
    }

    /**
     * Verify password for password-protected links
     */
    public function verifyPassword(Request $request, $slug)
    {
        try {
            $request->validate([
                'password' => 'required|string|max:255'
            ]);

            // Get the link
            $cacheService = app(RedisCacheService::class);
            $link = $cacheService->getCachedSlugLookup($slug);

            if (!$link) {
                $link = Link::where('slug', $slug)->first();
            }

            if (!$link) {
                return response()->json([
                    'success' => false,
                    'message' => 'Link not found.'
                ], 404);
            }

            if (!$link->is_password_protected) {
                return response()->json([
                    'success' => false,
                    'message' => 'This link is not password protected.'
                ], 400);
            }

            // Verify the password
            if ($link->verifyPassword($request->password)) {
                // Store verification in session
                $sessionKey = "link_password_verified_{$slug}";
                session([$sessionKey => true]);

                return response()->json([
                    'success' => true,
                    'message' => 'Password verified successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password. Please try again.'
                ], 401);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Password verification error', [
                'slug' => $slug,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while verifying the password.'
            ], 500);
        }
    }

    /**
     * Handle general errors consistently
     */
    protected function handleError(Request $request, string $message, string $type = 'error')
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'type' => $type
            ], 500);
        }

        return back()->withErrors(['general' => $message])->withInput();
    }

    /**
     * Get link analytics data
     */
    public function getLinkAnalytics(Request $request, $slug)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Get the link and ensure it belongs to the user
            $link = Link::where('slug', $slug)
                       ->where('user_id', $user->id)
                       ->first();

            if (!$link) {
                return response()->json([
                    'error' => 'Link not found or access denied'
                ], 404);
            }

            // Get visitor analytics data
            $visitors = $link->visitors()
                ->selectRaw('
                    DATE(created_at) as date,
                    COUNT(*) as clicks,
                    COUNT(DISTINCT ip_address) as unique_visitors
                ')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get();

            // Get geolocation data
            $geography = $link->visitors()
                ->selectRaw('
                    COALESCE(country, \'Unknown\') as country,
                    COUNT(*) as clicks
                ')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('country')
                ->orderBy('clicks', 'desc')
                ->limit(10)
                ->get();

            // Get device/browser data
            $devices = $link->visitors()
                ->selectRaw('
                    COALESCE(device_type, \'Unknown\') as device_type,
                    COUNT(*) as clicks
                ')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('device_type')
                ->orderBy('clicks', 'desc')
                ->get();

            $browsers = $link->visitors()
                ->selectRaw('
                    COALESCE(browser, \'Unknown\') as browser,
                    COUNT(*) as clicks
                ')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('browser')
                ->orderBy('clicks', 'desc')
                ->get();

            // Get referrer data
            $referrers = $link->visitors()
                ->selectRaw('
                    COALESCE(referrer, \'Direct\') as referrer,
                    COUNT(*) as clicks
                ')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('referrer')
                ->orderBy('clicks', 'desc')
                ->limit(10)
                ->get();

            $totalClicks = $link->visitors()->count();
            $uniqueVisitors = $link->visitors()->distinct('ip_address')->count('ip_address');
            $todayClicks = $link->visitors()->whereDate('created_at', today())->count();

            return response()->json([
                'link' => [
                    'id' => $link->id,
                    'slug' => $link->slug,
                    'url' => $link->url,
                    'short_url' => url($link->slug),
                    'title' => $link->title,
                    'description' => $link->description,
                    'created_at' => $link->created_at,
                ],
                'analytics' => [
                    'total_clicks' => $totalClicks,
                    'unique_visitors' => $uniqueVisitors,
                    'today_clicks' => $todayClicks,
                    'clicks_over_time' => $visitors,
                    'geography' => $geography,
                    'devices' => $devices,
                    'browsers' => $browsers,
                    'referrers' => $referrers,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to get link analytics', [
                'user_id' => auth()->id(),
                'slug' => $slug,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to retrieve analytics data'
            ], 500);
        }
    }

    /**
     * Update link (authenticated users only)
     */
    public function updateAuthenticated(Request $request, $id)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $link = Link::where('id', $id)
                       ->where('user_id', $user->id)
                       ->first();

            if (!$link) {
                return response()->json([
                    'success' => false,
                    'message' => 'Link not found or access denied'
                ], 404);
            }

            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:500',
                'is_disabled' => 'boolean',
                'expires_at' => 'nullable|date|after:now',
                'auto_delete_expired' => 'boolean',
            ]);

            $link->update($validated);

            return response()->json([
                'success' => true,
                'link' => [
                    'id' => $link->id,
                    'slug' => $link->slug,
                    'url' => $link->url,
                    'short_url' => url($link->slug),
                    'title' => $link->title,
                    'description' => $link->description,
                    'is_disabled' => $link->is_disabled,
                    'expires_at' => $link->expires_at,
                    'auto_delete_expired' => $link->auto_delete_expired,
                    'updated_at' => $link->updated_at,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'type' => 'validation_error'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to update link', [
                'user_id' => auth()->id(),
                'link_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update link'
            ], 500);
        }
    }

    /**
     * Delete link (authenticated users only)
     */
    public function destroyAuthenticated(Request $request, $id)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $link = Link::where('id', $id)
                       ->where('user_id', $user->id)
                       ->first();

            if (!$link) {
                return response()->json([
                    'success' => false,
                    'message' => 'Link not found or access denied'
                ], 404);
            }

            $link->delete();

            return response()->json([
                'success' => true,
                'message' => 'Link deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to delete link', [
                'user_id' => auth()->id(),
                'link_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete link'
            ], 500);
        }
    }
}
