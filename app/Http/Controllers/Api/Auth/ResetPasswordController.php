<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    public function __construct(private PasswordBroker $passwordBroker)
    {
    }

    public function forgot(Request $request)
    {
        $user = $this->passwordBroker->getUser($request->only('email'));
        if (is_null($user)) {
            return response()->json(['message' => 'Invalid user'], 422);
        }
        $token = $this->passwordBroker->createToken($user);
        $message = new ResetPassword($token);
        ResetPassword::$createUrlCallback = function () use ($request, $token, $user) {
            $params = http_build_query(['email' => $user->getEmailForPasswordReset()]);
            return $request->url . '/' . $token . '?' . $params;
        };
        $user->notify($message);
//        return response()->json(['message' => "Mail for reset password send to email $user->email"]);
        return response()->json(['data' => ['token' => $token]]);
    }

    public function reset(Request $request)
    {
        $reset_password_status = Password::reset(
            $request->only('email', 'token', 'password'),
            function ($user, $password) {
                $user->password = $password;
                $user->save();
            }
        );

        if ($reset_password_status == Password::INVALID_TOKEN) {
            return response()->json(['message' => 'Invalid token'], 422);
        } else {
            return response()->json(['message' => "Password for user $request->email successfully changed"]);
        }
    }
}
