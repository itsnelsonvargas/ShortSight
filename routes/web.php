<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Http;


use App\Http\Controllers\LinkController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SSOController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Vue SPA - Homepage
Route::get('/', function () {
    return view('app');
})->name('home');

// SSO Routes
Route::get('/auth/google',  [SSOController::class,'indexGoogle'])
    ->middleware('throttle:3,5')
    ->name('google.login');

Route::get('/auth/google/callback', [SSOController::class, 'storeGoogle'])
    ->name('google.callback');

Route::get('/auth/facebook', [SSOController::class, 'indexFacebook'])
    ->name('facebook.login');

Route::get('/auth/facebook/callback', [SSOController::class,'storeFacebook'])
    ->name('facebook.callback');

// Email Verification Routes
Route::get('/email/verify', function () {
    return view('app'); // Let Vue handle the email verification UI
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/dashboard?verified=1');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Illuminate\Http\Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('resent', true);
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Short URL redirect - must be first with specific constraints
Route::get('/{slug}',  [LinkController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-]{3,}')
    ->where('slug', '^(?!dashboard|login|register|auth|api).*$');

// Static pages
Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy-policy');

Route::get('/terms-of-service', function () {
    return view('terms-of-service');
})->name('terms-of-service');

// Catch all route for Vue SPA - must be last
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');






/**
 *  
 * For the endpoints of the API.
 * 
 */




 Route::get('/api/v1/link/{url}', [ApiController::class, 'getStoredLink'])
        ->name('getLink');












/**
 * Get visitor information based on request headers and user agent.
 *
 * @param Request $request
 * @return array
 */

function getVisitorInfo(Request $request): array {
    $agent = new Agent();
    $ipAddress = $request->ip() === '127.0.0.1' ? '103.192.185.61' : $request->ip();
    
    return [
        'ip_address' => $ipAddress,
        'user_agent' => $request->header('user-agent'),
        'browser'    => $agent->browser(),
        'device'     => getDeviceType($agent),
        'platform'   => $agent->platform(),
        'referer'    => $request->header('referer') ?? 'N/A',
        'location'   => getLocationData($ipAddress),
    ];
}

/**
 * Determine the device type based on user agent.
 *
 * @param Agent $agent
 * @return string
 */
function getDeviceType(Agent $agent): string {
    return match (true) {
        $agent->isMobile()  => 'Mobile',
        $agent->isTablet()  => 'Tablet',
        $agent->isDesktop() => 'Desktop',
        $agent->isRobot()   => 'Robot (Bot)',
        default             => 'Unknown Device',
    };
}

/**
 * Fetch location data from IP geolocation API.
 *
 * @param string $ipAddress
 * @return array
 */
function getLocationData(string $ipAddress): array {
    try {
        $response = Http::get("http://ipinfo.io/{$ipAddress}/json");

        if ($response->successful()) {
            $data = $response->json();
            $location = explode(',', $data['loc'] ?? ','); // Extract latitude and longitude

            return [
                'country'       => $data['country'] ?? null,
                'city'          => $data['city']    ?? null,
                'region'        => $data['region']  ?? null,
                'postal_code'   => $data['postal']  ?? null,
                'latitude'      => $location[0]     ?? null,
                'longitude'     => $location[1]     ?? null,
            ];
        }
    } catch (\Exception $e) {
        return [
            'country'     => null,
            'city'        => null,
            'region'      => null,
            'postal_code' => null,
            'latitude'    => null,
            'longitude'   => null,
        ];
    }
}




