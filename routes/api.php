<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\HealthCheckService;
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
Route::middleware('strict.throttle')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [UserController::class, 'store']);
});
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

/*
 * Get authenticated user
 */
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
 * GDPR Data Portability Routes
 */
Route::middleware(['auth:sanctum', 'api.throttle'])->group(function () {
    Route::get('/user/data-export', [UserController::class, 'getDataExportInfo']);
    Route::get('/user/data-export/download', [UserController::class, 'exportData']);
});

/*
 * Link Management Routes
 */
Route::middleware('link.creation.throttle')->post('/links', [LinkController::class, 'storeWithoutUserAccount']);
Route::middleware('api.throttle')->get('/check-slug', [LinkController::class, 'checkSlug']);
Route::middleware('api.throttle')->post('/links/{slug}/verify-password', [LinkController::class, 'verifyPassword']);

/*
 * Authenticated User Link Management Routes
 */
Route::middleware(['auth:sanctum', 'api.throttle'])->group(function () {
    // User links management
    Route::get('/user/links', [LinkController::class, 'getUserLinks']);
    Route::post('/user/links', [LinkController::class, 'storeAuthenticated']); // Authenticated link creation
    Route::get('/links/{id}', [LinkController::class, 'show']);
    Route::put('/links/{id}', [LinkController::class, 'updateAuthenticated']);
    Route::delete('/links/{id}', [LinkController::class, 'destroyAuthenticated']);

    // Link analytics
    Route::get('/links/{slug}/analytics', [LinkController::class, 'getLinkAnalytics']);

    // Slug availability check (authenticated)
    Route::get('/slug-available/{slug}', [LinkController::class, 'checkSlug']);
});

/*********************************
*                                *
* v1 API Routes                  *
*                                *
*********************************/
Route::middleware('api.throttle')->group(function () {
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
});

/*
 * Health Check Routes (Public)
 */
Route::get('/health', function () {
    $healthCheck = app(HealthCheckService::class);
    return response()->json($healthCheck->getSystemStatus());
});

Route::get('/health/full', function () {
    $healthCheck = app(HealthCheckService::class);
    return response()->json($healthCheck->performFullHealthCheck());
});
