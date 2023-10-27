<?php

use Illuminate\Support\Facades\Route;

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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group(['prefix' => '/oauth'], function () {
    Route::post('login', [\App\Http\Controllers\Api\Auth\OAuthController::class, 'token'])->name('oauth.login');
    Route::post('refresh', [\App\Http\Controllers\Api\Auth\OAuthController::class, 'refresh'])->name('oauth.refresh');
});

Route::post('register', \App\Http\Controllers\Api\Auth\RegisterController::class)->name('register');

//Routes for Sanctum Auth
//Route::post('login', \App\Http\Controllers\Api\Auth\LoginController::class)->name('login');
//Route::post('refresh', [\App\Http\Controllers\Api\Auth\LoginController::class, 'refresh'])->name('refresh');

Route::middleware('auth:api')->prefix('v1')->group(function () {
    require base_path('routes/api/api_v1.php');
});
