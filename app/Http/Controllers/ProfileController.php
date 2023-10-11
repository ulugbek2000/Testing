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


    function getProfile()
    {
        return response()->json(Auth::check() ? [auth()->user(), 200] : [null, 401]);
    }


    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $data = [];
        // return response()->json([$request->all(), $user]);

        if ($user->hasRole(UserType::Student, UserType::Teacher)) {
            // Валидация общих полей для Студента или Преподавателя

            $request->validate([
                'name' => 'string',
                'surname' => 'string',
                'email' => 'required_without:phone|email|unique:users,' . $user->id,
                'phone' => 'required_without:email|string|unique:users,' . $user->id,
                'password' => 'string|min:8',
                'city' => 'string',
                'photo' => 'nullable|mimes:jpeg,png,jpg,gif,mov',
                'gender' => 'string|in:male,female,other',
                'date_of_birth' => 'date',
            ]);
        }

        if ($user->hasRole(UserType::Teacher)) {

           $request->validate([
                'position' => 'nullable|string',
                'description' => 'nullable|string',
                'skills' => 'nullable|array', // Убедитесь, что это массив
                'skills.*' => 'image|mimes:jpeg,png,jpg,gif', // Проверка скиллов в виде изображений
            ]);

            $data['position'] = $request->input('position', $user->position);
            $data['description'] = $request->input('description', $user->description);
        }

        // $newPhone = $request->input('phone');
        // $newEmail = $request->input('email');

        // if ($request->has('phone')) {
        //     // Проверяем, существует ли новый номер телефона или email в базе данных
        //     if ($newPhone && $newPhone !== $user->phone && User::where('phone', $newPhone)->exists()) {
        //         return response()->json(['message' => 'The phone number is already in use'], 422);
        //     }
        // } else {
        //     $data['phone'] = $user->phone; // Используем значение из базы данных
        // }

        // if ($request->has('email')) {
        //     if ($newEmail && $newEmail !== $user->email && User::where('email', $newEmail)->exists()) {
        //         return response()->json(['message' => 'The email is already in use'], 422);
        //     }
        // } else {
        //     $data['email'] = $user->email; // Используем значение из базы данных
        // }

        $data = [
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'name' => $request->input('name', $user->name),
            'surname' => $request->input('surname', $user->surname),
            'city' => $request->input('city', $user->city),
            'gender' => $request->input('gender', $user->gender),
            'date_of_birth' => $request->input('date_of_birth', $user->date_of_birth),
        ];
        $user->update($data);
        $photoPath = $user->photo;

        if ($request->hasFile('photo')) {
            if (is_string($photoPath) && Storage::exists($photoPath)) {
                // Удалить старую фотографию
                Storage::delete($photoPath);
            }
            // Убедитесь, что файл был загружен
            $uploadedPhoto = $request->file('photo');

            // Создайте уникальное имя файла (вы можете изменить его по мере необходимости)
            $photoFileName = uniqid('photo_') . '.' . $uploadedPhoto->getClientOriginalExtension();

            // Сохраните новую фотографию со сгенерированным именем файла в каталоге public/photo.
            $photoPath = $uploadedPhoto->storeAs('photo', $photoFileName, 'public');

            // Обновите профиль пользователя, указав новый путь к фотографии.
            $data['photo'] = $photoPath;
        }


        if ($request->has('password')) {
            $user->password = bcrypt($request->input('password'));
            $user->save();
        }

        if ($user->hasRole(UserType::Teacher)) {

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
        if (UserType::Admin) {
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

            $newPhone = $request->input('phone');
            $newEmail = $request->input('email');

            // Проверяем, существует ли новый номер телефона или email в базе данных
            if ($request->has('phone')) {
                // Проверяем, существует ли новый номер телефона или email в базе данных
                if ($newPhone && $newPhone !== $user->phone && User::where('phone', $newPhone)->exists()) {
                    return response()->json(['message' => 'The phone number is already exist'], 422);
                }
            } else {
                $data['phone'] = $user->phone; // Используем значение из базы данных
            }

            if ($request->has('email')) {
                if ($newEmail && $newEmail !== $user->email && User::where('email', $newEmail)->exists()) {
                    return response()->json(['message' => 'The email is already exist'], 422);
                }
            } else {
                $data['email'] = $user->email; // Используем значение из базы данных
            }

            $data = [
                'name' => $request->input('name', $user->name),
                'surname' => $request->input('surname', $user->surname),
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
        $teachers = User::where('user_type', UserType::Teacher)
            ->with('userSkills')->get();

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
