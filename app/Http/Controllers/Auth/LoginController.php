<?php

namespace App\Http\Controllers\Auth;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Validation\Rule;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'role_id' => ['required', Rule::in(Role::client()->first()->id, Role::master()->first()->id)],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect(RouteServiceProvider::HOME);
        }

        return back()->withErrors([
            'master' => Role::where('id', $credentials['role_id'])->first()->role.' with this credentials do not exist.',
        ])->onlyInput('master');
    }
}
