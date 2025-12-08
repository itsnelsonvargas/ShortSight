<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*
 * Authentication Routes
 */
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [UserController::class, 'store']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

/*
 * Get authenticated user
 */
Route::middleware(['auth:sanctum', 'verified'])->get('/user', function (Request $request) {
    return $request->user();
});

/*
 * Account Management Routes
 */
Route::middleware(['auth:sanctum', 'verified'])->delete('/user/account', [UserController::class, 'destroy']);

/*
 * Link Management Routes
 */
Route::post('/links', [LinkController::class, 'storeWithoutUserAccount']);
Route::get('/check-slug', [LinkController::class, 'checkSlug']);

/*********************************
*                                *
* v1 API Routes                  *
*                                *
*********************************/
Route::get('/v1/ping', function () {
    return response()->json([
        'status'  => 'success',
        'message' => 'pong',
    ]);
});

Route::post('/v1/create-token', [ApiController::class, 'createToken'])
            ->name('api.createToken');

Route::get('/v1/delete-token', [ApiController::class, 'deleteToken'])
            ->name('api.deleteToken');

Route::get('/v1/link/{url}', [ApiController::class, 'getStoredLink'])
            ->name('api.getLink');

Route::get('/v1/check-slug', [ApiController::class, 'isSlugAvailable'])
            ->name('api.checkSlug');

Route::get('/v1/get-slug/{link}', [ApiController::class, 'getSlugOfLink'])
            ->name('api.getSlugOfLink');

Route::get('/v1/check-url/{url}', [ApiController::class, 'isUrlSafe'])
            ->name('api.checkUrl');
