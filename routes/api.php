<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

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
 *This route is used to get the authenticated user.
 */
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



/*********************************
*                                *
* Test if the API is working     *
* Already tested                 *
*                                *
*********************************/
Route::get('/v1/ping', function () {
    return response()->json([
        'status'  => 'success',
        'message' => 'pong',
    ]);
});

Route::get('/v1/create-token}', [ApiController::class, 'createToken'])
            ->name('createToken');

Route::get('/v1/delete-token}', [ApiController::class, 'deleteToken'])
            ->name('deleteToken');

Route::get('/v1/link/{url}', [ApiController::class, 'getStoredLink'])
            ->name('getLink');

Route::get('/v1/check-slug', [ApiController::class, 'isSlugAvailable'])
            ->name('checkSlug');

Route::get('/v1/get-slug/{link}', [ApiController::class, 'getSlugOfLink'])
            ->name('checkSlug');

Route::get('/v1/check-url/{url}', [ApiController::class, 'isUrlSafe'])
            ->name('checkSlug');
