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
        
        // Установите значения атрибутов пользователя на основе запроса
        $user->name = $request->input('name');
        $user->surname = $request->input('surname'); // Исправлено на 'surname'
        $user->email = $request->input('email'); // Исправлено на 'email'
        $user->phone = $request->input('phone');
        $user->city = $request->input('city');
        $user->gender = $request->input('gender');
        $user->date_of_birth = $request->input('date_of_birth');
        
        if ($request->has('password')) {
            $user->password = bcrypt($request->input('password'));
        }
        
      

        // if ($request->hasFile('photo')) {
        //     $destination = 'storage/profile/' . $user->photo;
        //     if (File::exists($destination)) {
        //         File::delete($destination);
        //     }
        //     $file = $request->file('photo');
        //     $extension = $file->getClientOriginalExtension();
        //     $filename = time() . '.' . $extension;
        //     $file->move('storage/profile/', $filename);
        //     $user->photo = $filename;
        // }
        if ($request->has('password')) {
            $user->password = bcrypt($request->input('password'));
        }
        $user->save();
        return response()->json(['message' => 'Profile updated successfully']);
    }
}
