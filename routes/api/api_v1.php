<?php
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
    ->middleware(['auth:api', 'scope:master', 'owner', 'sessionTz']);

Route::get('schedules', [\App\Http\Controllers\Api\V1\ScheduleController::class, 'getAllAvailable'])
    ->name('schedules.getAllAvailable')
    ->middleware(['sessionTz']);

Route::resource('users/{user}/appointments', \App\Http\Controllers\Api\V1\AppointmentController::class)
    ->only('index', 'show', 'destroy')
    ->middleware(['auth:api', 'scope:client,master', 'owner', 'sessionTz']);

Route::delete('users/{user}/schedules/{schedule}/appointment', [\App\Http\Controllers\Api\V1\ScheduleController::class, 'destroyAppointment'])
    ->name('schedules.destroyAppointment')
    ->middleware(['auth:api', 'scope:master', 'owner']);

Route::resource('services', \App\Http\Controllers\Api\V1\ServiceController::class)
    ->only('index');

Route::resource('users/{user}/carts', \App\Http\Controllers\Api\V1\CartController::class)
    ->only('index', 'store', 'destroy')
    ->middleware(['auth:api', 'owner', 'sessionTz']);

Route::get('users/{user}/checkout', [\App\Http\Controllers\Api\V1\CartController::class, 'checkout'])
    ->name('cart.checkout')
    ->middleware(['auth:api', 'owner', 'sessionTz']);

Route::get('users/{user}/button', [\App\Http\Controllers\Api\V1\CartController::class, 'getPayButton'])
    ->name('cart.getPayButton')
    ->middleware(['auth:api', 'owner', 'sessionTz']);

Route::post('appointment', [\App\Http\Controllers\Api\V1\AppointmentController::class, 'store'])
    ->name('appointment.store')
    ->middleware(['sessionTz']);
