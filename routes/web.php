<?php
 //SSO
 

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Http;


use App\Http\Controllers\LinkController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SSOController;
use App\Http\Controllers\ApiController;

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


Route::get('/testqrr/{slug}', [LinkController::class, 'downloadPng'])
    ->name('downloadPng');



Route::get('/',[LinkController::class, 'create'])
    ->name('home');

Route::post('/', [LinkController::class, 'storeWithoutUserAccount'])
    ->name('createLinkWithoutUserAccount');

Route::get('/logout', [AuthController::class,'logout'])
    ->name('logout');

Route::get('/auth/google', [SSOController::class,'indexGoogle'])
    ->name('google.login');

Route::get('/auth/google/callback', [SSOController::class, 'storeGoogle'])
    ->name('google.callback');




Route::get('/login/facebook', [SSOController::class, 'indexFacebook'])
    ->name('facebook.login');
    
Route::get('/auth/facebook/callback', [SSOController::class,'storeFacebook'])
    ->name('facebook.callback');



    Route::get('/privacy-policy', function () {
        return view('privacy-policy');
    });
    

Route::get('/test',  [LinkController::class, 'test'] );

Route::get('/qr',  [LinkController::class, 'testQR'] );


Route::get('/{slug}',  [LinkController::class, 'show']   );

Route::post('/check-slug',  [LinkController::class, 'checkSlug'] )
    ->name('checkSlug');

Route::get('/redirect-ad-page',  function (Request $request) {
    return view('redirectAdPage');
})->name('redirectAdPage');






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




