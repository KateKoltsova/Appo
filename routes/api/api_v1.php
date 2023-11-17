<?php
//Route::get('/user', function () {
//    return response()->json(['data'=>['message'=>'Hello! You are success authenticate']]);
//});

Route::resource('users', \App\Http\Controllers\Api\V1\UserController::class)
    ->only('index', 'show')
    ->middleware(['auth:api']);
Route::resource('users', \App\Http\Controllers\Api\V1\UserController::class)
    ->only('update', 'destroy')
    ->middleware(['auth:api', 'owner']);

Route::resource('users/{user}/prices', \App\Http\Controllers\Api\V1\PriceController::class)
    ->except('create', 'edit')
    ->middleware(['auth:api', 'owner']);

Route::resource('users/{user}/schedules', \App\Http\Controllers\Api\V1\ScheduleController::class)
    ->except('create', 'edit')
    ->middleware(['auth:api', 'owner']);

Route::resource('services', \App\Http\Controllers\Api\V1\ServiceController::class)
    ->only('index')
    ->middleware(['auth:api']);
