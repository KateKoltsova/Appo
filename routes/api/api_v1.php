<?php
Route::get('/user', function () {
    return response()->json(['data'=>['message'=>'Hello! You are success authenticate']]);
});
