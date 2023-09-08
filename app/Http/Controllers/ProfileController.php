<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make(request()->all(), [
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

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = [
            'name' => $request->input('name'),
            'surname' => $request->input('surname'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'city' => $request->input('city'),
            'gender' => $request->input('gender'),
            'date_of_birth' => $request->input('date_of_birth'),
        ];
        $user->update($updateData);
        // $coverpath = $user->photo;

        // if ($request->hasFile('photo')) {
        //     // Delete old cover file if needed
        //     Storage::delete($user->photo);
        //     // Upload and store new cover file
        //     $coverpath = $request->file('photo')->store('photo', 'public');
        // }

        // $data = array_merge($request->only(['name', 'type', 'topic_id', 'duration']), [
        //     'photo' => $coverpath,

        // ]);

        // $user->update($data);

        // if ($request->has('password')) {
        //     $user->password = bcrypt($request->input('password'));
        //     $user->save();
        // }
        return response()->json(['message' => 'Profile updated successfully']);
    }
}
