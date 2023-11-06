<?php
//Route::get('/user', function () {
//    return response()->json(['data'=>['message'=>'Hello! You are success authenticate']]);
//});

Route::middleware('auth:api')->resource('user', \App\Http\Controllers\Api\V1\UserController::class);

Route::resource('service', \App\Http\Controllers\Api\V1\ServiceController::class);
