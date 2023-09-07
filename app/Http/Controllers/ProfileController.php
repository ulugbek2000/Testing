<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {

        $user = Auth::user();
        $request->validate([
            'name' => 'string',
            'surname' => 'string',
            'email' => 'email|unique:users,email,' . $user->id,
            'phone' => 'string|unique:users,phone,' . $user->id,
            'password' => 'string|min:8',
            'city' => 'string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,mov',
            'gender' => 'string|in:male,female,other',
            'date_of_birth' => 'date',
        ]);

        $user->name = $request->input('name');
        $user->surname = $request->input('surname');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->city = $request->input('city');
        // $user->photo = $request->input('photo');
        $user->gender = $request->input('gender');
        $user->date_of_birth = $request->input('date_of_birth');
        $photoPath = $user->photo;
        if ($request->hasFile('photo')) {
            // Upload and store new cover file
            $photoPath = $request->file('photo')->store('photo', 'public');
        }
        $data = array_merge($request->only( [
            'photo' => $photoPath
        ]));
        if ($request->has('password')) {
            $user->password = bcrypt($request->input('password'));
        }

        $user->save($data);

        return response()->json(['message' => 'Profile updated successfully']);
    }
}
