<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'assign'], function () {
    Route::post('role', [\App\Http\Controllers\Api\V1\PermissionController::class, 'assignRole'])
        ->name('assignRole')
        ->middleware(['auth:api', 'permission:permissions.read-all|permissions.create-other|permissions.delete-other']);
    Route::post('roles', [\App\Http\Controllers\Api\V1\PermissionController::class, 'assignRoles'])
        ->name('assignRoles')
        ->middleware(['auth:api', 'permission:permissions.read-all|permissions.create-other|permissions.delete-other']);
});

Route::resource('permissions', \App\Http\Controllers\Api\V1\PermissionController::class)
    ->middleware(['auth:api', 'permission:permissions.read-all|permissions.create-other|permissions.delete-other']);

Route::group(['prefix' => 'permissions'], function () {
    Route::post('assign-role', [\App\Http\Controllers\Api\V1\PermissionController::class, 'assignRolePermissions'])
        ->name('assignRolePermissions')
        ->middleware(['auth:api', 'permission:permissions.read-all|permissions.create-other|permissions.delete-other']);
    Route::post('assign-user', [\App\Http\Controllers\Api\V1\PermissionController::class, 'assignUserPermissions'])
        ->name('assignUserPermissions')
        ->middleware(['auth:api', 'permission:permissions.read-all|permissions.create-other|permissions.delete-other']);
});

Route::get('roles', [\App\Http\Controllers\Api\V1\UserController::class, 'rolesList'])
    ->middleware(['auth:api', 'permission:roles.read-all']);

Route::post('admin/master', [\App\Http\Controllers\Api\V1\AdminController::class, 'createMaster'])
    ->name('masterRegister')
    ->middleware(['auth:api', 'permission:users.create-other']);

Route::resource('users', \App\Http\Controllers\Api\V1\UserController::class)
    ->only('index')
    ->middleware(['auth:api', 'permission:users.read-all']);

Route::resource('users', \App\Http\Controllers\Api\V1\UserController::class)
    ->only('show', 'update', 'destroy')
    ->middleware(['auth:api']);

Route::resource('users/{user}/prices', \App\Http\Controllers\Api\V1\PriceController::class)
    ->except('create', 'edit')
    ->middleware(['auth:api']);

Route::resource('users/{user}/schedules', \App\Http\Controllers\Api\V1\ScheduleController::class)
    ->except('create', 'edit')
    ->middleware(['auth:api', 'sessionTz']);

Route::get('schedules', [\App\Http\Controllers\Api\V1\ScheduleController::class, 'getAllAvailable'])
    ->name('schedules.getAllAvailable')
    ->middleware(['sessionTz']);

Route::resource('users/{user}/appointments', \App\Http\Controllers\Api\V1\AppointmentController::class)
    ->only('index', 'store', 'show', 'destroy')
    ->middleware(['auth:api', 'sessionTz']);

Route::delete('users/{user}/schedules/{schedule}/appointment', [\App\Http\Controllers\Api\V1\ScheduleController::class, 'destroyAppointment'])
    ->name('schedules.destroyAppointment')
    ->middleware(['auth:api']);

Route::resource('services', \App\Http\Controllers\Api\V1\ServiceController::class)
    ->only('index');

Route::resource('services', \App\Http\Controllers\Api\V1\ServiceController::class)
    ->only('store', 'update', 'destroy')
    ->middleware(['auth:api', 'permission:services.create-other|services.create-own|services.delete-other|services.delete-own|services.update-other|services.update-own']);

Route::resource('users/{user}/carts', \App\Http\Controllers\Api\V1\CartController::class)
    ->only('index', 'store', 'destroy')
    ->middleware(['auth:api', 'sessionTz']);

Route::get('users/{user}/checkout', [\App\Http\Controllers\Api\V1\CartController::class, 'checkout'])
    ->name('cart.checkout')
    ->middleware(['auth:api', 'sessionTz']);

Route::get('users/{user}/button', [\App\Http\Controllers\Api\V1\CartController::class, 'getPayButton'])
    ->name('cart.getPayButton')
    ->middleware(['auth:api', 'sessionTz']);

Route::post('callback', [\App\Http\Controllers\Api\V1\AppointmentController::class, 'callback'])
    ->name('callback')
    ->middleware(['sessionTz']);

Route::post('users/{user}/avatar', [\App\Http\Controllers\Api\V1\UserController::class, 'loadAvatar'])
    ->name('users.avatar')
    ->middleware(['auth:api', 'sessionTz']);

Route::delete('users/{user}/avatar', [\App\Http\Controllers\Api\V1\UserController::class, 'deleteAvatar'])
    ->name('users.deleteAvatar')
    ->middleware(['auth:api', 'sessionTz']);

Route::resource('users/{user}/galleries', \App\Http\Controllers\Api\V1\GalleryController::class)
    ->only('index', 'show')
    ->middleware(['sessionTz']);

Route::resource('users/{user}/galleries', \App\Http\Controllers\Api\V1\GalleryController::class)
    ->only('store', 'destroy')
    ->middleware(['auth:api', 'sessionTz']);

Route::get('statuses/{order}', function ($order) {
    $liqpay = new \App\Services\LiqpayService();
    $resp = $liqpay->getResponse($order);
    return response()->json(['data' => $resp]);
});
