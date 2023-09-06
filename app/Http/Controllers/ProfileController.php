<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'name' => 'string',
            'surname' => 'string',
            'email' => 'email|unique:users,email,' . $user->id,
            'phone' => 'string|unique:users,phone,' . $user->id,
            'password' => 'string|min:8',
            'city' => 'string',
            'photo' => 'mimes:jpeg,png,jpg,gif,mov',
            'gender' => 'string|in:male,female,other',
            'date_of_birth' => 'date',

        ]);

        $user->update($validatedData);

        return response()->json(['message' => 'Profile updated succesfully']);
    }
}
