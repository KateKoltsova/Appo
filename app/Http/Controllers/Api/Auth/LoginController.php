<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        if (!auth()->attempt($credentials)) {
            return response()->json(['data' => ['message' => 'User with that credentials not found']], 401);
        }
        $token = auth()->user()->createToken($request->device_name ?? 'api')->plainTextToken;
        return response()->json(['data' => ['token' => $token]]);
    }
}
