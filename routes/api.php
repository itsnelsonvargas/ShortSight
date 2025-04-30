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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/api/v1/link/{url}', [ApiController::class, 'getStoredLink'])
->name('getLink');

Route::get('/api/v1/check-slug/{slug}', [ApiController::class, 'isSlugAvailable'])
->name('checkSlug');

Route::get('/api/v1/check-slug/test12',  function(){return 'test';});