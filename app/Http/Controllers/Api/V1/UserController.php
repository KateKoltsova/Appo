<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoadAvatarRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use App\Services\Api\UserService;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService,
    )
    {
    }

    public function rolesList()
    {
        try {
            return response()->json($this->userService->getRolesList());
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?? 404);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('view', User::class);
            $filters['role_id'] = $request->input('filter.role_id');
            return response()->json($this->userService->getList($filters));
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?? 404);
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
        try {
            $this->authorize('view', [User::class, $id]);
            $response = $this->userService->getById($id);
            return response()->json($response);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?? 404);
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
        try {
            $this->authorize('update', [User::class, $id]);
            $params = $request->validated();
            $response = $this->userService->update($params, $id);
            return response()->json($response);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?? 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->authorize('delete', [User::class, $id]);
            $this->userService->delete($id);
            return response()->json(['message' => 'User successfully deleted']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?? 404);
        }
    }

    public function loadAvatar(LoadAvatarRequest $request, string $id)
    {
        try {
            $this->authorize('update', [User::class, $id]);
            $user = User::findOrFail($id);
            $params = $request->validated();
            $this->userService->loadAvatar($params['image'], $user);
            return response()->json(['message' => 'User avatar successfully loaded']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?? 404);
        }
    }

    public function deleteAvatar(string $id)
    {
        try {
            $this->authorize('update', [User::class, $id]);
            $user = User::findOrFail($id);
            $this->userService->deleteAvatar($user);
            return response()->json(['message' => 'User avatar successfully deleted']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?? 404);
        }
    }
}
