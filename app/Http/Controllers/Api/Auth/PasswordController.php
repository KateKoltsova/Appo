<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\AuthService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordController extends Controller
{
    public function __construct(private PasswordBroker $passwordBroker)
    {
    }

    public function forgot(ForgotPasswordRequest $request)
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

    public function reset(ResetPasswordRequest $request)
    {
        if (!($user = Password::getUser($request->only('email')))) {
            return response()->json(['message' => 'Invalid user'], 422);
        }
        if (!Password::tokenExists($user, $request->token)) {
            return response()->json(['message' => 'Invalid user'], 422);
        }
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

    public function change(Request $request)
    {
        $credentials = $request->validate([
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string'],
        ]);
        $user = $request->user();

        if (!Hash::check($credentials['old_password'], $user->password)) {
            return response()->json(['message' => 'The given data was invalid'], 422);
        } else {
            $user->password = $credentials['new_password'];
            $user->save();

            $oauth = new OAuthController(new AuthService());
            $logout = $oauth->logoutAll();

            if (!$logout) {
                return response()->json(['message' => $logout->original['message']]);
            }

            return response()->json(['message' => 'Password successfully changed']);
        }
    }
}
