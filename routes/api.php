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

Route::post('login', \App\Http\Controllers\Api\Auth\LoginController::class)->name('login');
Route::post('register', \App\Http\Controllers\Api\Auth\RegisterController::class)->name('register');

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    require base_path('routes/api/api_v1.php');
});
