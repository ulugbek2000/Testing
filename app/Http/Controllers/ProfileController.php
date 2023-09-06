<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            'photo' => 'mimes:jpeg,png,jpg,gif,mov',
            'gender' => 'string|in:male,female,other',
            'date_of_birth' => 'date',

        ]);
        $photoPath = $user->photo;
        if ($request->hasFile('photo')) {
            // Delete old photo file if needed
            Storage::delete($user->photo);
            // Upload and store new photo file
            $photoPath = $request->file('photo')->store('account', 'public');
        }

        $data = array_merge($request->only(['name', 'surname', 'email', 'phone', 'password', 'city', 'photo', 'gender', 'date_of_birth']), [
            'photo' => $photoPath
        ]);
        $user->update($data);

        return response()->json(['message' => 'Profile updated succesfully']);
    }
}
