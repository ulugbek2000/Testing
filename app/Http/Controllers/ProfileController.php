<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\User;
use App\Models\UserSkills;
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
        $validator = null; // Инициализация валидатора

        if (UserType::Student || UserType::Teacher) {
            // Валидация общих полей для Студента и Преподавателя
            $validator = Validator::make($request->all(), [
                'name' => 'string',
                'surname' => 'string',
                'email' => 'required_without:phone|email|unique:users,email,' . $user->id,
                'phone' => 'required_without:email|string|unique:users,phone,' . $user->id,
                'password' => 'string|min:8',
                'city' => 'string',
                'photo' => 'nullable|mimes:jpeg,png,jpg,gif,mov',
                'gender' => 'string|in:male,female,other',
                'date_of_birth' => 'date',
            ]);
        }

        if (UserType::Teacher) {

            $validator = Validator::make($request->all(), [
                'position' => 'nullable|string',
                'description' => 'nullable|string',
                'skills' => 'nullable|array', // Убедитесь, что это массив
                'skills.*' => 'image|mimes:jpeg,png,jpg,gif', // Проверка скиллов в виде изображений
            ]);
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'name' => $request->input('name', $user->name),
            'surname' => $request->input('surname', $user->surname),
            'email' => $request->input('email', $user->email),
            'phone' => $request->input('phone', $user->phone),
            'city' => $request->input('city', $user->city),
            'gender' => $request->input('gender', $user->gender),
            'date_of_birth' => $request->input('date_of_birth', $user->date_of_birth),
        ];

        $photoPath = $user->skills;
        if (is_string($photoPath) && Storage::exists($photoPath)) {
            Storage::delete($photoPath);
            $photoPath = $request->file('skills')->store('skills', 'public');
            $data['skills'] = $photoPath;
        }

        if (UserType::Teacher) {
            $data['position'] = $request->input('position', $user->position);
            $data['description'] = $request->input('description', $user->description);
        }

        $user->update($data);

        if ($request->has('password')) {
            $user->password = bcrypt($request->input('password'));
            $user->save();
        }

        if (UserType::Teacher) {
            // dd($request->file('skills'), $request->has('skills'), is_array($request->file('skills')));

            if ($request->has('skills') && is_array($request->file('skills'))) {
                foreach ($request->file('skills') as $skillImage) {

                    if ($skillImage->isValid()) {
                        $skillPath = $skillImage->store('skills', 'public');

                        UserSkills::create([
                            'user_id' => $user->id,
                            'skills' => $skillPath,
                        ]);
                    }
                }
            }
        }

        return response()->json(['message' => 'Profile updated successfully']);
    }
}
