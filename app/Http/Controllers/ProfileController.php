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
            'photo' => 'nullable|mimes:jpeg,png,jpg,gif,mov',
            'gender' => 'string|in:male,female,other',
            'date_of_birth' => 'date',
        ]);

        $photoPath = $user->photo;


        if ($request->hasFile('photo')) {
            // Delete old cover file if needed
            Storage::delete($user->photo);
            // Upload and store new cover file
            $photoPath = $request->file('photo')->store('photo', 'public');
        }
        $data = array_merge($request->only(['name', 'surname', 'email', 'phone', 'city', 'gender', 'date_of_birth']), [
            'photo' => $photoPath
        ]);

        $user->update($data);

        if ($request->has('password')) {
            $user->password = bcrypt($request->input('password'));
            $user->save();
        }

        return response()->json(['message' => 'Profile updated successfully']);
    }
}
