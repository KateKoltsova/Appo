<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Contracts\AuthTokenGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Token;

class OAuthController extends Controller
{
    public const TYPE_PASSWORD = 'password';

    public const TYPE_REFRESH = 'refresh_token';

    public function __construct(private AuthTokenGenerator $tokenGenerator)
    {
    }

    public function token(Request $request)
    {
        $response = $this->tokenGenerator->generateTokens($this->credentials($request), static::TYPE_PASSWORD);
        if ($response->status() !== Response::HTTP_OK) {
            return response()->json(['message' => 'The given data was invalid'], 422);
        }
        $data = $this->getUserId($response);
        return response()->json($data, $response->status());
    }

    public function refresh(Request $request)
    {
        $response = $this->tokenGenerator->generateTokens($request->only('refresh_token'), static::TYPE_REFRESH);
        if ($response->status() !== Response::HTTP_OK) {
            return response()->json(['message' => 'The given data was invalid'], 422);
        }
        $data = $this->getUserId($response);
        return response()->json($data, $response->status());
    }

    private function credentials(Request $request): array
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);
        return [
            'username' => $credentials['email'],
            'password' => $credentials['password'],
        ];
    }

    private function getUserId($response)
    {
        $data['data'] = $response->json();
        $access_token = $data['data']['access_token'];
        $token_parts = explode('.', $access_token);
        $token_header_json = base64_decode($token_parts[1]);
        $token_header_array = json_decode($token_header_json, true);
        $token_id = $token_header_array['jti'];
        $token = Token::find($token_id);
        $user = $token->user;
        $user_id = $user->id;
        $role = $user->role->toArray();
        $token->update(['scopes' => [$role['role']]]);
        $data['data']['id'] = $user_id;
        return $data;
    }
}
