<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
}
}