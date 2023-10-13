<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Http\Resources\USerResource;
use App\Models\Role;
use App\Models\User;
use App\Models\UserSkills;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
        $validator = null;
        if ($user->hasRole(UserType::Student)) {
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
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->only([
            'email',
            'phone',
            'name',
            'surname',
            'city',
            'gender',
            'date_of_birth',
        ]));
        $photoPath = $user->photo;

        if (is_string($photoPath) && Storage::exists($photoPath)) {
            // Удалить старую фотографию
            Storage::delete($photoPath);
        }

        if ($request->hasFile('photo')) {

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
        // $user->update($data);
        return response()->json(['message' => 'Profile updated successfully']);
    }
    // if ($user->hasRole(UserType::Teacher)) {

    //    $request->validate([
    //         'position' => 'nullable|string',
    //         'description' => 'nullable|string',
    //         'skills' => 'nullable|array', // Убедитесь, что это массив
    //         'skills.*' => 'image|mimes:jpeg,png,jpg,gif', // Проверка скиллов в виде изображений
    //     ]);

    //     $data['position'] = $request->input('position', $user->position);
    //     $data['description'] = $request->input('description', $user->description);
    // }

    // $newPhone = $request->input('phone');
    // $newEmail = $request->input('email');

    // if ($user->hasRole(UserType::Teacher)) {

    //     if ($request->has('skills') && is_array($request->file('skills'))) {
    //         foreach ($request->file('skills') as $skillImage) {
    //             if ($skillImage->isValid()) {
    //                 $skillPath = $skillImage->store('skills', 'public');
    //                 UserSkills::create([
    //                     'user_id' => $user->id,
    //                     'skills' => $skillPath,
    //                 ]);
    //             }
    //         }
    //     }
    // }


    public function updateTeacher(Request $request, User $user)
    {
        // $user = Auth::user();
        if ($user->hasRole(UserType::Admin)) {
            $request->validate([
                'name' => 'string',
                'surname' => 'string',
                'email' => 'required_without:phone|email|unique:users' . $user->id,
                'phone' => 'required_without:email|string|unique:users' . $user->id,
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
        }

        $path = $user->photo;

        if ($request->hasFile('photo')) {
            // Delete old cover file if needed
            Storage::delete($user->photo);
            // Upload and store new cover file
            $path = $request->file('photo')->store('photoMentor', 'public');
        }
        $data = array_merge(
            $request->only(['name', 'email', 'phone', 'surname', 'city', 'gender', 'date_of_birth', 'position', 'description',]),
            ['photo' => $path]
        );

        $user->update($data);

        if ($request->has('password')) {
            $user->password = bcrypt($request->input('password'));
            $user->save();
        }

        // Log::info('All Files', $allFiles);
        //! Get the user's current skills
        $currentSkills = $user->userSkills->pluck('skills')->all();

        $requestData = $request->all();

        //! Create an array containing the names of the files loaded from the front
        $uploadedSkillNames = [];

        foreach ($requestData as $name => $data) {
            if (str_contains($name, 'user_skills')) {
                if ($data instanceof UploadedFile && $data->isValid()) {
                    $skillName = $data->getClientOriginalName();
                    $skillPath = $data->store('skills', 'public');
                    UserSkills::create([
                        'user_id' => $user->id,
                        'skills' => $skillPath,
                    ]);
                    $uploadedSkillNames[] = $skillPath;
                }else {
                    $uploadedSkillNames[] = $data;
                }
            }
        }

        //! Remove skills that were not loaded from the front
        $currentSkills = UserSkills::where('user_id', $user->id)->whereNotIn('skills', $uploadedSkillNames)->delete();
                  
        return response()->json(['message' => 'The files skills are updated successfully.']);

        if ($user->hasRole(!UserType::Admin)) {
            return response()->json(['error' => 'Access denied'], 403);
        }
    }

    public function getAllStudents(User $user)
    {
        // $user = Auth::user();
        if ($user->hasRole(UserType::Student)) {
            $students = User::all();
        }
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
        if ($user->hasRole(UserType::Teacher)) {
            $user->load('userSkills');
        }

        // Вернем данные о пользователе
        return response()->json(['user' => $user], 200);
    }
}
