<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'assign'], function () {
    Route::post('role', [\App\Http\Controllers\Api\V1\PermissionController::class, 'assignRole'])
        ->name('assignRole')
        ->middleware(['auth:api']);
    Route::post('roles', [\App\Http\Controllers\Api\V1\PermissionController::class, 'assignRoles'])
        ->name('assignRoles')
        ->middleware(['auth:api']);
});

Route::resource('permissions', \App\Http\Controllers\Api\V1\PermissionController::class)
    ->middleware(['auth:api']);

Route::group(['prefix' => 'permissions'], function () {
    Route::post('assign-role', [\App\Http\Controllers\Api\V1\PermissionController::class, 'assignRolePermissions'])
        ->name('assignRolePermissions')
        ->middleware(['auth:api']);
    Route::post('assign-user', [\App\Http\Controllers\Api\V1\PermissionController::class, 'assignUserPermissions'])
        ->name('assignUserPermissions')
        ->middleware(['auth:api']);
});

Route::get('roles', [\App\Http\Controllers\Api\V1\UserController::class, 'rolesList'])
    ->middleware(['auth:api', 'scope:admin']);

Route::post('admin/master', [\App\Http\Controllers\Api\V1\AdminController::class, 'createMaster'])
    ->name('masterRegister')
    ->middleware(['auth:api', 'scope:admin']);

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
    ->only('index', 'store', 'show', 'destroy')
    ->middleware(['auth:api', 'scope:client,master', 'owner', 'sessionTz']);

Route::delete('users/{user}/schedules/{schedule}/appointment', [\App\Http\Controllers\Api\V1\ScheduleController::class, 'destroyAppointment'])
    ->name('schedules.destroyAppointment')
    ->middleware(['auth:api', 'scope:master', 'owner']);

Route::resource('services', \App\Http\Controllers\Api\V1\ServiceController::class)
    ->only('index');

Route::resource('services', \App\Http\Controllers\Api\V1\ServiceController::class)
    ->only('store', 'update', 'destroy')
    ->middleware(['auth:api', 'scope:admin']);

Route::resource('users/{user}/carts', \App\Http\Controllers\Api\V1\CartController::class)
    ->only('index', 'store', 'destroy')
    ->middleware(['auth:api', 'owner', 'sessionTz']);

Route::get('users/{user}/checkout', [\App\Http\Controllers\Api\V1\CartController::class, 'checkout'])
    ->name('cart.checkout')
    ->middleware(['auth:api', 'owner', 'sessionTz']);

Route::get('users/{user}/button', [\App\Http\Controllers\Api\V1\CartController::class, 'getPayButton'])
    ->name('cart.getPayButton')
    ->middleware(['auth:api', 'owner', 'sessionTz']);

Route::post('callback', [\App\Http\Controllers\Api\V1\AppointmentController::class, 'callback'])
    ->name('callback')
    ->middleware(['sessionTz']);

Route::post('users/{user}/avatar', [\App\Http\Controllers\Api\V1\UserController::class, 'loadAvatar'])
    ->name('users.avatar')
    ->middleware(['auth:api', 'owner', 'sessionTz']);

Route::delete('users/{user}/avatar', [\App\Http\Controllers\Api\V1\UserController::class, 'deleteAvatar'])
    ->name('users.deleteAvatar')
    ->middleware(['auth:api', 'owner', 'sessionTz']);

Route::resource('users/{user}/galleries', \App\Http\Controllers\Api\V1\GalleryController::class)
    ->only('index', 'show')
    ->middleware(['auth:api', 'sessionTz']);

Route::resource('users/{user}/galleries', \App\Http\Controllers\Api\V1\GalleryController::class)
    ->only('store', 'destroy')
    ->middleware(['auth:api', 'scope:master', 'owner', 'sessionTz']);

Route::get('statuses/{order}', function ($order) {
    $liqpay = new \App\Services\LiqpayService();
    $resp = $liqpay->getResponse($order);
    return response()->json(['data' => $resp]);
});
