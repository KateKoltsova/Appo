<?php

namespace App\Http\Controllers\Auth;

use App\Models\Role;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
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
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect(RouteServiceProvider::HOME);
        }

        return back();
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $client = new Client();
        $response = $client->post('https://appobeauty-6ecbc596ee8d.herokuapp.com/api/oauth/login',
            [
                'form_params' => [
                    'email' => $request->input('email'),
                    'password' => $request->input('password'),
                ],
                'headers' => [
                    'Accept' => 'application / json',
                ]
            ]
        );

        if ($response->getStatusCode() == 200) {

            $tokens = json_decode($response->getBody(), true);

            Session::put('access_token', $tokens['data']['access_token']);
            Session::put('refresh_token', $tokens['data']['refresh_token']);
            Session::put('id', $tokens['data']['id']);
            Cookie::make('access_token', $tokens['data']['access_token']);
            Cookie::make('refresh_token', $tokens['data']['refresh_token']);
            Cookie::make('id', $tokens['data']['id']);

            return redirect('/home');
        }
    }
}
