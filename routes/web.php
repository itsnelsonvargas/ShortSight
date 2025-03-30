<?php
 //SSO
 use Laravel\Socialite\Facades\Socialite;
 use App\Models\User;
 use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\LinkController;
 
 

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




// Redirect user to Google OAuth page
Route::get('/auth/google', function () {
    return Socialite::driver('google')->redirect();
});

// Handle callback from Google
Route::get('/auth/google/callback', function () {
    $googleUser = Socialite::driver('google')->user();

    // Find or create the user in your database
    $user = User::updateOrCreate(
        ['email' => $googleUser->getEmail()], // Check if user exists by email
        [
            'name' => $googleUser->getName(),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
        ]
    );

    // Log in the user
    Auth::login($user);

    return redirect('/'); // Redirect after login
});



Route::get('/', function () {
    return view('welcome');
});

Route::post('/', [LinkController::class, 'storeWithoutUserAccount'])->name('createLinkWithoutUserAccount');

 


Route::get('/test', function (Request $request) {
    return response()->json(getVisitorInfo($request));
});


Route::get('/{slug}',  [LinkController::class, 'show']   );


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
        'browser' => $agent->browser(),
        'device' => getDeviceType($agent),
        'platform' => $agent->platform(),
        'referer' => $request->header('referer') ?? 'N/A',
        'location' => getLocationData($ipAddress),
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
        $agent->isMobile() => 'Mobile',
        $agent->isTablet() => 'Tablet',
        $agent->isDesktop() => 'Desktop',
        $agent->isRobot() => 'Robot (Bot)',
        default => 'Unknown Device',
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
                'country' => $data['country'] ?? null,
                'city' => $data['city'] ?? null,
                'region' => $data['region'] ?? null,
                'postal_code' => $data['postal'] ?? null,
                'latitude' => $location[0] ?? null,
                'longitude' => $location[1] ?? null,
            ];
        }
    } catch (\Exception $e) {
        return [
            'country' => null,
            'city' => null,
            'region' => null,
            'postal_code' => null,
            'latitude' => null,
            'longitude' => null,
        ];
    }
}



Route::get('/auth/google', function () {
    return Socialite::driver('google')->redirect();
})->name('google.login');

Route::get('/auth/google/callback', function () {
    $googleUser = Socialite::driver('google')->stateless()->user(); // Client error: `POST https://www.googleapis.com/oauth2/v4/token` resulted in a `400 Bad Request` response: { "error": "invalid_request", "error_description": "Missing required parameter: code" }
 
    $user = User::updateOrCreate([
        'email' => $googleUser->getEmail(),
    ], [
        'name' => $googleUser->getName(),
        'password' => bcrypt('random_password'),
        'google_id' => $googleUser->getId(),
    ]);

    Auth::login($user);

    //SSO completed
    return 'nice'; // Redirect after login
});