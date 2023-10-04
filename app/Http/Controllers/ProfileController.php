<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Http\Resources\USerResource;
use App\Models\Role;
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


    function getProfile() {
        return response()->json(Auth::check() ? [auth()->user(), 200] : [null, 401]);
    }


    public function updateProfile(Request $request)
    {

        $user = Auth::user();
        $validator = null; // Инициализация валидатора

        if (UserType::Student || UserType::Teacher) {
            // Валидация общих полей для Студента или Преподавателя
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

        $photoPath = $user->photo;

        if (is_string($photoPath) && Storage::exists($photoPath)) {
            // Delete the old photo
            Storage::delete($photoPath);
        }

        if ($request->hasFile('photo')) {
            // Ensure that a file was uploaded
            $uploadedPhoto = $request->file('photo');

            // Generate a unique filename (you can modify this as needed)
            $photoFileName = uniqid('photo_') . '.' . $uploadedPhoto->getClientOriginalExtension();

            // Store the new photo with the generated filename in the 'public/photo' directory
            $photoPath = $uploadedPhoto->storeAs('photo', $photoFileName, 'public');

            // Update the user's profile with the new photo path
            $data['photo'] = $photoPath;
        }

        // $photoPath = $user->photo;
        // if (is_string($photoPath) && Storage::exists($photoPath)) {
        //     Storage::delete($photoPath);
        //     $photoPath = $request->file('photo')->store('photo', 'public');
        //     $data['photo'] = $photoPath;
        // }

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


    public function updateTeacher(Request $request, User $user)
    {
        // if (UserType::Admin)
        if ($user->user_type === 'admin') {
            $validator = null;
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
                'position' => 'nullable|string',
                'description' => 'nullable|string',
                'skills' => 'nullable|array',
                'skills.*' => 'image|mimes:jpeg,png,jpg,gif',
            ]);


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
                'position' => $request->input('position', $user->position),
                'description' => $request->input('description', $user->description),
            ];

            $photoPath = $user->photo;
            if (is_string($photoPath) && Storage::exists($photoPath)) {
                Storage::delete($photoPath);
                $photoPath = $request->file('photo')->store('photo', 'public');
                $data['photo'] = $photoPath;
            }

            if ($request->has('password')) {
                $user->password = bcrypt($request->input('password'));
                $user->save();
            }

            $user->update($data);
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

            return response()->json(['message' => 'Mentor updated successfully']);
            if (!UserType::Admin) {
                return response()->json(['error' => 'Access denied'], 403);
            }
        }
    }

    public function getAllStudents()
    {
        $students = User::all()->filter(function ($user) {
            return $user->user_type === UserType::Student;
        });

        return response()->json($students);
    }

    public function getAllTeachers()
    {
        $teachers = User::where('user_type', UserType::Teacher)->with('userSkills')->get();

        return response()->json($teachers);
    }

    public function getUserById(Request $request, User $user)
    {
        if (!$user) {
            // Если пользователь не найден, вернем сообщение об ошибке
            return response()->json(['message' => 'User not found'], 404);
        }

        // Проверим, является ли пользователь учителем
        if ($user->user_type == UserType::Teacher) {
            $user->load('userSkills');
        }

        // Вернем данные о пользователе
        return response()->json(['user' => $user], 200);
    }
}
