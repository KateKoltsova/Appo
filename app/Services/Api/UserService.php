<?php

namespace App\Services\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\ImageService;
use Exception;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\RefreshToken;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private ImageService   $imageService,
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
        try {
            $user = User::findOrFail($userId);

            DB::beginTransaction();

            if (isset($params['image'])) {

                $this->loadAvatar($params['image'], $user);

                unset($params['image']);
            }

            $user->update($params);

            DB::commit();

            return $this->getById($userId);

        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage(), $e->getCode());
        }
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

            $this->imageService->delete($user->image_url);

            $user->delete();

            return true;

        } else {
            throw new Exception('User not found', 404);
        }
    }

    public function loadAvatar($image, User $user)
    {
        if (!is_null($user->image_url)) {
            $this->imageService->delete($user->image_url);
        }

        $image_url = $this->imageService->upload($image);

        $user->image_url = $image_url['data']['url'];

        return $user->save();
    }

    public function deleteAvatar(User $user)
    {
        if (!is_null($user->image_url)) {
            $this->imageService->delete($user->image_url);

            $user->image_url = null;

            return $user->save();
        } else {
            throw new Exception('Avatar is not found', 404);
        }
    }
}
