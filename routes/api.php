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
    Route::delete('logout', [\App\Http\Controllers\Api\Auth\OAuthController::class, 'logout'])->name('oauth.logout')
        ->middleware(['auth:api']);
    Route::delete('logout/all', [\App\Http\Controllers\Api\Auth\OAuthController::class, 'logoutAll'])->name('oauth.logoutAll')
        ->middleware(['auth:api']);
});

Route::group(['prefix' => '/password'], function () {
    Route::post('forgot', [\App\Http\Controllers\Api\Auth\PasswordController::class, 'forgot'])->name('password.forgot');
    Route::post('reset', [\App\Http\Controllers\Api\Auth\PasswordController::class, 'reset'])->name('password.reset');
    Route::post('change', [\App\Http\Controllers\Api\Auth\PasswordController::class, 'change'])->name('password.change')
        ->middleware(['auth:api']);
});

Route::post('register', \App\Http\Controllers\Api\Auth\RegisterController::class)->name('register');

//Routes for Sanctum Auth
//Route::post('login', \App\Http\Controllers\Api\Auth\LoginController::class)->name('login');
//Route::post('refresh', [\App\Http\Controllers\Api\Auth\LoginController::class, 'refresh'])->name('refresh');

Route::prefix('v1')->group(function () {
    require base_path('routes/api/api_v1.php');
});
