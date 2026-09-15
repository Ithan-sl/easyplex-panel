<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


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
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ])->status(422);
    }

    protected function authenticated(Request $request, $user)
    {
        if (!$user->isAdmin()) {
            auth()->logout();
            return back()->with('error', 'You are not authorized to access this area.');
        }
    }

    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);
        $attempt = $this->guard()->attempt(
            $credentials, $request->filled('remember')
        );

        if (!$attempt) {
            return false;
        }

        $user = $this->guard()->user();
        if (!$user->isAdmin()) {
            $this->guard()->logout();
            return false;
        }

        return true;
    }
}
