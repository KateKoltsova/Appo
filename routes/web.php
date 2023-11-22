<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

//Auth::routes();

Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');

Route::get('/password/forgot', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->name('password.request');
Route::post('/password/forgot', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->name('password.email');
Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->name('password.reset');
Route::post('/password/reset', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->name('password.update');


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
