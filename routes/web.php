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
    ->middleware('strict.throttle:3,5') // 3 attempts per 5 minutes for SSO
    ->name('google.login');

Route::get('/auth/google/callback', [SSOController::class, 'storeGoogle'])
    ->name('google.callback');

Route::get('/auth/facebook', [SSOController::class, 'indexFacebook'])
    ->name('facebook.login');

Route::get('/auth/facebook/callback', [SSOController::class,'storeFacebook'])
    ->name('facebook.callback');

// Short URL redirect - must be first with specific constraints
Route::get('/{slug}',  [LinkController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-]{3,}')
    ->where('slug', '^(?!dashboard|login|register|auth|api).*$');

// Catch all route for Vue SPA - must be last
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

/*
 * API Endpoints
 */
Route::get('/api/v1/link/{url}', [ApiController::class, 'getStoredLink'])
    ->name('getLink');