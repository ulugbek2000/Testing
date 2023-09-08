<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Проверяем данные из тела запроса
        $validator = Validator::make($request->all(), [
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
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Обновляем данные пользователя на основе JSON-данных
        $data = [
            'name' => $request->input('name', $user->name),
            'surname' => $request->input('surname', $user->surname),
            'email' => $request->input('email', $user->email),
            'phone' => $request->input('phone', $user->phone),
            // 'password' => $request->has('password') ? bcrypt($request->input('password')) : $user->password,
            'city' => $request->input('city', $user->city),
            'gender' => $request->input('gender', $user->gender),
            'date_of_birth' => $request->input('date_of_birth', $user->date_of_birth),
        ];

        if (is_string($photoPath) && Storage::exists($photoPath)) {
            Storage::delete($photoPath);
            $photoPath = $request->file('photo')->store('photo', 'public');
            $data['photo'] = $photoPath;
        } 
        $photoPath = $request->file('photo')->store('photo', 'public');
        $data['photo'] = $photoPath;
        $user->update($data);
       

        if ($request->has('password')) {
            $user->password = bcrypt($request->input('password'));
            $user->save();
        }

        return response()->json(['message' => 'Profile updated successfully']);











        // public function updateProfile(Request $request)
        // {
        //     // dd($request->all());
        //     $user = Auth::user();

        //     $request->validate([
        //         'name' => 'string',
        //         'surname' => 'string',
        //         'email' => 'email|unique:users,email,' . $user->id,
        //         'phone' => 'string|unique:users,phone,' . $user->id,
        //         'password' => 'string|min:8',
        //         'city' => 'string',
        //         'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,mov',
        //         'gender' => 'string|in:male,female,other',
        //         'date_of_birth' => 'date',
        //     ]);

        //     $user = Auth::user();

        //     $request->validate([
        //         'name' => 'string',
        //         'surname' => 'string',
        //         'email' => 'email|unique:users,email,' . $user->id,
        //         'phone' => 'string|unique:users,phone,' . $user->id,
        //         'password' => 'string|min:8',
        //         'city' => 'string',
        //         'photo' => 'nullable|mimes:jpeg,png,jpg,gif,mov',
        //         'gender' => 'string|in:male,female,other',
        //         'date_of_birth' => 'date',
        //     ]);
        //     $coverpath = $user->photo;
        //     if ($request->hasFile('photo')) {
        //         // Delete old logo file if needed
        //         Storage::delete($user->photo);
        //         // Upload and store new logo file
        //         $coverpath = $request->file('photo')->store('photo', 'public');
        //     } else {
        //         $coverpath = $user->photo;
        //     }

        //     $data = array_merge($request->only(['name', 'surname', 'email', 'phone', 'city', 'gender', 'date_of_birth']), [
        //         'photo' => $coverpath,

        //     ]);
        //     $user->update($data);
        //     if ($request->has('password')) {
        //         $user->password = bcrypt($request->input('password'));
        //         $user->save();
        //     }

        //     return response()->json(['message' => 'Profile updated successfully']);
        // }
    }
}
