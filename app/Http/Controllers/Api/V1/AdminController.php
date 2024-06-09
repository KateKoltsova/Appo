<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Notifications\MasterResetPasswordNotification;
use Exception;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function __construct(
        private PasswordBroker $passwordBroker
    )
    {
    }

    //TODO: refactor to use service for reset password
    public function createMaster(Request $request)
    {
        try {
            $requestData = $request->only(['email', 'phone_number']);
            $defaultValues = config('constants.db.default_master');

            $params = [
                'firstname' => $defaultValues['firstname'],
                'lastname' => $defaultValues['lastname'],
                'email' => $requestData['email'],
                'phone_number' => $requestData['phone_number'],
                'password' => $defaultValues['password'],
            ];

            $validator = Validator::make($params, (new RegisterRequest())->rules());

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            DB::beginTransaction();

            $role = Role::master()->first();

            $newMaster = $role->users()->create($params);

            if (is_null($newMaster)) {
                throw new Exception('Invalid user', 422);
            }

            $user = $this->passwordBroker->getUser($params);

            $token = $this->passwordBroker->createToken($user);

            $message = new MasterResetPasswordNotification($token);
            MasterResetPasswordNotification::$createUrlCallback = function () use ($request, $token, $user) {
                $params = http_build_query(['email' => $user->getEmailForPasswordReset()]);
                return $request->url . '/' . $token . '?' . $params;
            };
            $user->notify($message);

            DB::commit();

            return response()->json(['data' => ['token' => $token]]);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()]);
        }

//        return response()->json(['message' => 'Master successfully created and received link to set password'], 201);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
