<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\Contracts\AuthTokenGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Passport\RefreshToken;
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
        $accessToken = $data['data']['access_token'];
        $tokenId = $this->getTokenId($accessToken);
        $token = Token::find($tokenId);
        $user = $token->user;
        $userId = $user->id;
        $role = $user->role->toArray();
        $token->update(['scopes' => [$role['role']]]);
        $data['data']['id'] = $userId;
        return $data;
    }

    public function logout(Request $request)
    {
        $accessToken = explode('Bearer ', $request->header('Authorization'));
        $tokenId = $this->getTokenId($accessToken[1]);
        Token::find($tokenId)->revoke();
        RefreshToken::firstWhere('access_token_id', $tokenId)->revoke();
        return response()->json(['message' => 'User successfully logout']);
    }

    public function logoutAll()
    {
        $user = auth()->user();
        $accessTokens = $user->tokens->each->revoke();
        foreach ($accessTokens as $accessToken) {
            RefreshToken::firstWhere('access_token_id', $accessToken->id)->revoke();
        }
        return response()->json(['message' => 'User successfully logout from all devices']);
    }

    private function getTokenId($accessToken)
    {
        $tokenParts = explode('.', $accessToken);
        $tokenHeaderJson = base64_decode($tokenParts[1]);
        $tokenHeaderArray = json_decode($tokenHeaderJson, true);
        return $tokenHeaderArray['jti'];
    }
}
