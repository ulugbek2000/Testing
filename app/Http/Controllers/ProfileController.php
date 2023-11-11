<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Http\Resources\USerResource;
use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use App\Models\UserLessonsProgress;
use App\Models\UserSkills;
use App\Models\UserSubscription;
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

        $request->validate([
            'email' => 'required_without:phone|email|unique:users,email,' . $user->id,
            'phone' => 'required_without:email|string|unique:users,phone,' . $user->id,
            'name' => 'string',
            'surname' => 'string',
            'password' => ['string', 'min:8', 'confirmed'],
            'city' => 'string',
            'photo' => 'nullable|file',
            'gender' => 'string|in:male,female,other',
            'date_of_birth' => 'date',
        ]);

        $path = $user->photo;

        if ($request->hasFile('photo')) {
            // Delete old cover file if needed
            if ($user->photo !== null) {
                Storage::delete($user->photo);
            }
            // Upload and store new cover file
            $path = $request->file('photo')->store('photoMentor', 'public');
        } elseif (!$request->has('photo') && $user->photo !== null) {
            // Delete old photo file if no new photo is sent
            Storage::delete($user->photo);
            $path = null; // Set path to null when no new photo is sent
        }
        $data = array_merge(
            $request->only(['name', 'email', 'phone', 'surname', 'city', 'gender', 'date_of_birth']),
            ['photo' => $path]
        );
        // Обновление профиля пользователя
        $user->update($data);

        if ($request->has('password')) {
            $user->password = bcrypt($request->input('password'));
            $user->save();
        }

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
                'email' => 'required_without:phone|email|unique:users,' . $user->id,
                'phone' => 'required_without:email|string|unique:users,' . $user->id,
                'password' => ['string', 'min:8', 'confirmed'],
                'city' => 'string',
                'photo' => 'nullable|file',
                'gender' => 'string|in:male,female,other',
                'date_of_birth' => 'date',
                'position' => 'nullable|string',
                'description' => 'nullable|text',
                'skills' => 'nullable|array',
                'skills.*' => 'image|mimes:jpeg,png,jpg,gif',
            ]);
        }

        $path = $user->photo;

        if ($request->hasFile('photo')) {
            // Delete old cover file if needed
            if ($user->photo !== null) {
                Storage::delete($user->photo);
            }
            // Upload and store new cover file
            $path = $request->file('photo')->store('photoMentor', 'public');
        }
        $data = array_merge(
            $request->only(['name', 'email', 'phone', 'surname', 'city', 'gender', 'date_of_birth', 'position', 'description']),
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
                } else {
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

    public function getAllStudents()
    {
        $students = User::role(UserType::Student)->with('subscriptions.subscription', 'subscriptions.course')->get();

        $studentData = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'surname' => $student->surname,
                'email' => $student->email,
                'phone' => $student->phone,
                'city' => $student->city,
                'photo' => $student->photo,
                'user_type' => $student->user_type,
                'gender' => $student->gender,
                'description' => $student->description,
                'position' => $student->position,
                'date_of_birth' => $student->date_of_birth,
                'subscriptions' => $student->subscriptions->map(function ($subscription) use ($student) {

                    $totalLessons = $subscription->course->lessons()->count();

                    $completedLessons = UserLessonsProgress::where('user_id', $student->id)
                        ->where('course_id', $subscription->course->id)
                        ->where('completed', true)
                        ->count();

                    $progressPercentage = $totalLessons > 0 ? ($completedLessons * 100 / $totalLessons) : 0;

                    return [
                        'course' => [
                            'id' => $subscription->course->id,
                            'logo' => $subscription->course->logo,
                            'name' => $subscription->course->name,
                            'slug' => $subscription->course->slug,
                            'quantity_lessons' => $subscription->course->quantity_lessons,
                            'hours_lessons' => $subscription->course->hours_lessons,
                            'short_description' => $subscription->course->short_description,
                            'video' => $subscription->course->video,
                            'has_certificate' => $subscription->course->has_certificate,
                        ],
                        'subscription_id' => $subscription->id,
                        'subscription_name' => $subscription->subscription->name,
                        'subscription_price' => $subscription->price,
                        'completed_lessons' => $completedLessons,
                        'total_lessons' => $totalLessons,
                        'progress_percentage' => $progressPercentage,
                        'deleted_at' => $subscription->deleted_at,
                    ];
                }),
            ];
        });

        return response()->json($studentData);
    }


    public function getStudentsSubscription()
    {
        $subscriptions = UserSubscription::with([
            'user:id,name,surname,photo,phone',
            'subscription:id,name,price,duration,duration_type',
            'subscription.description',
            'course:id,name,slug,quantity_lessons,hours_lessons,short_description,video,has_certificate,logo',
        ])->select('id', 'user_id', 'subscription_id', 'course_id', 'deleted_at', 'created_at')->get();

        $filteredSubscriptions = $subscriptions->map(function ($subscription) {
            return [
                'id' => $subscription->id,
                'name' => $subscription->user->name,
                'surname' => $subscription->user->surname,
                'photo' => $subscription->user->photo,
                'phone' => $subscription->user->phone,
                'subscription' => [
                    'name' => $subscription->subscription->name,
                    'price' => $subscription->subscription->price,
                    'duration' => $subscription->subscription->duration,
                    'duration_type' => $subscription->subscription->duration_type,
                    'created_at' => $subscription->created_at,
                    'deleted_at' => $subscription->deleted_at,
                    'description' => $subscription->subscription->description->pluck('description'),
                ],
                'course' => $subscription->course
                    ? [
                        'name' => $subscription->course->name,
                        'slug' => $subscription->course->slug,
                        'quantity_lessons' => $subscription->course->quantity_lessons,
                        'hours_lessons' => $subscription->course->hours_lessons,
                        'short_description' => $subscription->course->short_description,
                        'video' => $subscription->course->video,
                        'has_certificate' => $subscription->course->has_certificate,
                        'logo' => $subscription->course->logo,
                    ]
                    : null
            ];
        })->toArray();

        return response()->json($filteredSubscriptions);
    }


    public function getAllTeachers()
    {
        $teachers = User::whereHas('roles', function ($query) {
            $query->where('id', UserType::Teacher);
        })->with('userSkills')->get();
        return response()->json($teachers);
    }

    public function getUserById(User $user)
    {
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        if ($user->hasRole(UserType::Teacher)) {
            $user->load('userSkills');
        }

        return response()->json(['user' => $user], 200);
    }


    function getCourseWithEnroledUsers()
    {
        $subscriptions = UserSubscription::with([
            'user:id,name,surname,photo',
            'subscription:id,name,price,duration,duration_type',
            'course:id,name,slug,quantity_lessons,hours_lessons,logo',
        ])->select('id', 'user_id', 'subscription_id', 'course_id', 'deleted_at', 'created_at')->get();

        $filteredSubscriptions = $subscriptions->map(function ($subscription) {
            return [
                'id' => $subscription->id,
                'name' => $subscription->user->name,
                'surname' => $subscription->user->surname,
                'photo' => $subscription->user->photo,
                'subscription' => [
                    'name' => $subscription->subscription->name,
                    'price' => $subscription->subscription->price,
                    'duration' => $subscription->subscription->duration,
                    'duration_type' => $subscription->subscription->duration_type,
                    'created_at' => $subscription->created_at,
                    'deleted_at' => $subscription->deleted_at,
                    // 'description' => $subscription->subscription->description->pluck('description'),
                ],
                'course' => $subscription->course
                    ? [
                        'name' => $subscription->course->name,
                        'slug' => $subscription->course->slug,
                        'quantity_lessons' => $subscription->course->quantity_lessons,
                        'hours_lessons' => $subscription->course->hours_lessons,
                        // 'short_description' => $subscription->course->short_description,
                        // 'video' => $subscription->course->video,
                        // 'has_certificate' => $subscription->course->has_certificate,
                        'logo' => $subscription->course->logo,
                    ]
                    : null
            ];
        })->toArray();

        return response()->json($filteredSubscriptions);
    }
}
