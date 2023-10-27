<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\PairTokens;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        if (!auth()->attempt($credentials)) {
            return response()->json(['message' => 'User with that credentials not found'], 401);
        }
        $accessToken = auth()->user()->createToken($request->device_name ?? 'api', ['access-api'])->plainTextToken;
        $refreshToken = auth()->user()->createToken($request->device_name ?? 'api', ['issue-access-token'])->plainTextToken;
        $access_token_id = PersonalAccessToken::findToken($accessToken)->id;
        $refresh_token_id = PersonalAccessToken::findToken($refreshToken)->id;
        PairTokens::create([
            'access_token_id' => $access_token_id,
            'refresh_token_id' => $refresh_token_id
        ]);
        return response()->json(['data' => ['accessToken' => $accessToken, 'refreshToken' => $refreshToken]]);
    }

    public function refresh(Request $request)
    {
        return dd($request);
        $token = explode('Bearer ', $request->header('Authorization'));
        $findToken = PersonalAccessToken::findToken($token[1]);
        if (empty($token[1]) || empty($findToken) || !$findToken->can('issue-access-token')) {
            return response()->json(['message' => 'Token is invalid'], 422);
        }
        if (!$findToken->tokenable instanceof User) {
            return response()->json(['message' => 'Token is invalid'], 422);
        }

        //Deleting old pair tokens (access and refresh)
        $pairTokens = PairTokens::firstWhere('refresh_token_id', $findToken->id);
        $access_token_id = $pairTokens->access_token_id;
        $accessToken = PersonalAccessToken::firstWhere('id', $access_token_id);
        $accessToken->delete();
        $findToken->delete();
        $pairTokens->delete();

        //Creating new pair tokens (access and refresh)
        $accessToken = $findToken->tokenable->createToken($request->device_name ?? 'api', ['access'])->plainTextToken;
        $refreshToken = $findToken->tokenable->createToken($request->device_name ?? 'api', ['refresh'])->plainTextToken;
        $access_token_id = PersonalAccessToken::findToken($accessToken)->id;
        $refresh_token_id = PersonalAccessToken::findToken($refreshToken)->id;
        PairTokens::create([
            'access_token_id' => $access_token_id,
            'refresh_token_id' => $refresh_token_id
        ]);
        return response()->json(['data' => ['token' => $accessToken, 'refreshToken' => $refreshToken]]);
    }
}
