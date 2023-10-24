<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function __invoke(Request $request)
    {
        $params = $request->validate([
            'firstname' => ['required', 'string', 'max:50'],
            'lastname' => ['required', 'string', 'max:50'],
            'birthdate' => ['nullable', 'date'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:App\Models\User'],
            'phone_number' => ['required', 'string', 'min:13', 'max:13', 'regex:/^\+380[0-9]{9}$/', 'unique:App\Models\User'],
            'password' => ['required', 'string', 'min:3'],
        ]);
        $role = Role::client()->first();
        $role->users()->create($params);
        return response()->json(['data' => ['Successfully Created']], 201);
    }
}
