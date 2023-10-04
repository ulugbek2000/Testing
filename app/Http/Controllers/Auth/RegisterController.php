<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string',
            'surname' => 'required|string',
            'email' => 'required_without:phone|email|unique:users',
            'phone' => 'required_without:email|string|unique:users',
            'password' => 'required|string|min:8',
            'city' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,mov',
            'gender' => 'nullable|string|in:male,female,other',
            'date_of_birth' => 'nullable|date',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $photoPath = null;

        if (isset($data['photo']) && $data['photo']->isValid()) {
            $photoPath = $data['photo']->store('photo', 'public');
        }
        return User::create([
            'name' => $data['name'],
            'surname' => $data['surname'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'city' => $data['city'],
            'gender' => $data['gender'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'photo' => $photoPath,
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        // event(new Registered($user = $this->create($request->all())));
        $user = $this->create($request->all());


        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);

        // $token = $this->guard()->login($user);

        // if ($response = $this->registered($request, $user)) {
        //     return $response;
        // }

        // $token = $user->createToken('api-token')->plainTextToken;
        // $role = $user->roles()->first()->id;
        // $cookie = cookie('jwt', $token);
        // $response = [
        //     'message' => $token,
        //     'user_role' => $role,
        //     'is_phone_verified' => $user->phone_verified_at != null
        // ];

        $response = [
            'message' =>  $token
        ];

        return $request->wantsJson()
                    ? new JsonResponse([$response], 201)
                    : redirect($this->redirectPath());
    }
}
