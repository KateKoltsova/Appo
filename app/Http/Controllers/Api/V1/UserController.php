<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Passport\RefreshToken;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = explode('?', $request->getRequestUri());
        if (isset($query[1])) {
            $query = explode('&', $query[1]);
            foreach ($query as $value) {
                $value = explode('filter[role]=', $value);
                $filters[] = $value[1];
            }
            $users = User::with('role')
                ->whereHas('role', function ($query) use ($filters) {
                    $query->whereIn('role', $filters);
                })
                ->get();
        } else {
            $users = User::with('role')
                ->get();
        }
        return response()->json(['data' => $users]);
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
        return response()->json(['data' => $user]);
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
    public function update(Request $request, string $id)
    {
        if (auth()->user()->id == $id) {
            $params = $request->validate([
                'firstname' => ['string', 'alpha', 'max:50'],
                'lastname' => ['string', 'alpha', 'max:50'],
                'birthdate' => ['nullable', 'date'],
                'email' => ['string', 'email', 'max:255', Rule::unique('users')->ignore($id)],
                'phone_number' => ['string', 'min:13', 'max:13', 'regex:/^\+380[0-9]{9}$/', Rule::unique('users')->ignore($id)],
            ]);
            User::findOrFail($id)->update($params);
            return $this->show($id);
        } else {
            return response()->json(['message' => 'You don\'t have permissions to make this action'], Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (auth()->user()->id == $id) {
            $user = User::findOrFail($id);
            $accessTokens = $user->tokens->each->revoke();
            foreach ($accessTokens as $accessToken) {
                RefreshToken::firstWhere('access_token_id', $accessToken->id)->revoke();
            }
            $user->delete();
            return response()->json(['message' => 'User successfully deleted']);
        } else {
            return response()->json(['message' => 'You don\'t have permissions to make this action'], Response::HTTP_FORBIDDEN);
        }

    }
}
