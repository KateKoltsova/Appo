<?php
//Route::get('/user', function () {
//    return response()->json(['data'=>['message'=>'Hello! You are success authenticate']]);
//});

Route::resource('users', \App\Http\Controllers\Api\V1\UserController::class)
    ->only('index')
    ->middleware(['auth:api', 'scope:admin']);
Route::resource('users', \App\Http\Controllers\Api\V1\UserController::class)
    ->only('show', 'update', 'destroy')
    ->middleware(['auth:api', 'owner']);

Route::resource('users/{user}/prices', \App\Http\Controllers\Api\V1\PriceController::class)
    ->except('create', 'edit')
    ->middleware(['auth:api', 'scope:master', 'owner']);

Route::resource('users/{user}/schedules', \App\Http\Controllers\Api\V1\ScheduleController::class)
    ->except('create', 'edit')
    ->middleware(['auth:api', 'scope:master', 'owner']);

Route::resource('users/{user}/appointments', \App\Http\Controllers\Api\V1\AppointmentController::class)
    ->only('index', 'show', 'destroy')
    ->middleware(['auth:api', 'scope:client', 'owner']);

Route::resource('services', \App\Http\Controllers\Api\V1\ServiceController::class)
    ->only('index')
    ->middleware(['auth:api', 'scope:admin,master']);
