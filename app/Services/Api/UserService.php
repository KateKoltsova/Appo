<?php

namespace App\Services\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\UserRepository;
use Exception;
use Laravel\Passport\RefreshToken;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function getList($filters)
    {
        $users = $this->userRepository->getAll($filters);

        $userCollection = UserResource::collection($users);

        return ['data' => $userCollection];

    }

    /**
     * Display the specified resource.
     */
    public function getById(string $userId)
    {
        $user = $this->userRepository->getById($userId);

        if (!empty($user)) {
            $userResource = UserResource::make($user);

            return ['data' => $userResource];
        } else {
            throw new Exception('User not found', 404);

        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($params, string $userId)
    {
        User::findOrFail($userId)->update($params);

        return $this->getById($userId);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $userId)
    {
        $user = User::findOrFail($userId);

        if (!empty($user)) {
            $accessTokens = $user->tokens->each->revoke();

            foreach ($accessTokens as $accessToken) {
                RefreshToken::firstWhere('access_token_id', $accessToken->id)->revoke();
            }

            $user->delete();

            return true;

        } else {
            throw new Exception('User not found', 404);
        }
    }
}
