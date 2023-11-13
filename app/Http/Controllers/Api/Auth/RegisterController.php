<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Role;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request)
    {
        $params = $request->validated();
        $role = Role::client()->first();
        $role->users()->create($params);
        return response()->json(['message' => 'Successfully Created'], 201);
    }
}
