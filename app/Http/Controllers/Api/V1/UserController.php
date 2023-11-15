<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Passport\RefreshToken;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $roles = $request->input('filter.role') ?? ['master'];
        $users = User::with('role')
            ->whereHas('role', function ($query) use ($roles) {
                $query->whereIn('role', $roles);
            })
            ->get();
        if (!empty($users)) {
            $userCollection = new UserCollection($users);
            return response()->json(['data' => $userCollection]);
        } else {
            return response()->json(['message' => 'No data'], 404);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::where('id', $id)
            ->with('role')
            ->first();
        if (!empty($user)) {
            $userResource = new UserResource($user);
            return response()->json(['data' => $userResource]);
        } else {
            return response()->json(['message' => 'No data'], 404);

        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, string $id)
    {
        $params = $request->validated();
        User::findOrFail($id)->update($params);
        return $this->show($id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $accessTokens = $user->tokens->each->revoke();
        foreach ($accessTokens as $accessToken) {
            RefreshToken::firstWhere('access_token_id', $accessToken->id)->revoke();
        }
        $user->delete();
        return response()->json(['message' => 'User successfully deleted']);
    }
}
