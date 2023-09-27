<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

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
        $this->middleware(['guest'])->except('logout');
    }

    // function handleUserWasAuthenticated(Request $request, $throttles) {
        
    // }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;
        $role = $user->roles()->first()->id;
        $cookie = cookie('jwt', $token);
        $response = [
            'message' => $token,
            'user_role' => $role,
            'is_phone_verified' => $user->phone_verified_at != null
        ];

        return $request->wantsJson()
                    ? new JsonResponse([$response], 204)
                    : redirect()->intended($this->redirectPath());
    }
}
