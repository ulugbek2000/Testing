<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;
use app\Http\Requests\CourseStoreRequest;
use App\Http\Resources\CourseResource;
use App\Models\CourseSkills;
use App\Models\CourseSubscription;
use App\Models\User;
use App\Models\UserCourse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Nette\Utils\Random;
use Symfony\Contracts\Service\Attribute\Required;

class CourseController extends Controller
{

    // function __construct()
    // {
    //     $this->middleware('course');
    // }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {


        $per_page = $request->per_page ?? 12;
        // All Courses
        // $perPage = $request->input('per_page', 2);
        // $courses = Course::all();
        // $courses = Course::paginate($perPage);
        // $courses->setPath(url('/api/courses'));
        // // Return Json Response
        // return response()->json([
        //     'courses' => $courses
        // ], 200);

        return CourseResource::collection(Course::paginate($per_page));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'logo' => 'required|mimes:jpeg,png,jpg,gif,mov',
            'name' => 'required|string',
            'slug' => 'required|string|max:255',
            'quantity_lessons' => 'required',
            'hours_lessons' => 'required',
            'short_description' => 'required',
            // 'duration' => 'required|integer',
            // 'duration_type' => 'required',
            'video' => 'required|mimes:mp4,mov,avi,mpeg,mkv',
        ]);
        $logo = $request->file('logo')->store('images', 'public');
        $video = $request->file('video')->store('videos', 'public');

        // Сохранение видео в папку storage/app/public/videos

        try {
            $course = Course::create([
                'logo' => Storage::url($logo),
                'name' => $request->name,
                'slug' => $request->slug,
                'quantity_lessons' => $request->quantity_lessons,
                'hours_lessons' => $request->hours_lessons,
                'short_description' => $request->short_description,
                // 'duration' => $request->duration,
                // 'duration_type' => $request->duration_type,
                'video' => Storage::url($video),
                'has_certificate' => $request->has_certificate,
            ]);
            CourseSubscription::create([
                'course_id' => $request->$course->id,
                'subscription_id' => $request->subscription_id,
            ]);

            return response()->json([
                'message' => "Course succefully created."
            ], 200);
        } catch (\Exception $e) {
            //Return response Json
            return response()->json([
                'message' => $e,
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        return new CourseResource($course);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'quantity_lessons' => 'required|string',
            'hours_lessons' => 'required|string',
            'short_description' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,mov',
            'video' => 'nullable|mimes:mp4,mov,avi,mpeg,mkv,max:102400',
        ]);
        if ($request->hasFile('logo')) {
            // Delete old logo file if needed
            Storage::delete($course->logo);
            // Upload and store new logo file
            $logopath = $request->file('logo')->store('images', 'public');
        } else {
            $logopath = $course->logo;
        }
        // Handle video file update
        if ($request->hasFile('video')) {
            // Delete old video file if needed
            Storage::delete($course->video);
            // Upload and store new video file
            $videopath = $request->file('video')->store('videos', 'public');
        } else {
            $videopath = $course->video;
        }
        $data = array_merge($request->only(['name', 'slug', 'short_description', 'quantity_lessons', 'hours_lessons', 'has_certificate']), [
            'logo' => $logopath,
            'video' => $videopath,
        ]);

        $course->update($data);

        return response()->json(['message' => 'Course updated successfuly'], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        $course->delete();
        return response()->json([
            'message' => "Course successfuly deleted."
        ], 200);
    }

    public function enroll(Request $request, Course $course, User $user)
    {
        $userCourse = UserCourse::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course->id
        ], [
            'user_id' => $user->id,
            'course_id' => $course->id
        ]);
        return response()->json(['message' => $userCourse->wasRecentlyCreated ? "User enrolled to course successfuly." : "User already enrolled!"], 200);
    }

    public function addTeachersToCourse(Request $request, Course $course)
    {
        $teacherIds = $request->input('teacher_ids', []);

        // Получите идентификаторы текущих учителей курса
        $currentTeacherIds = $course->users()
            ->whereHas('roles', function ($query) {
                $query->where('name', UserType::Teacher);
            })
            ->pluck('users.id')
            ->toArray();
        // Определите идентификаторы учителей для удаления
        $teachersToRemove = array_diff($currentTeacherIds, $teacherIds);

        // Удалите учителей, которых нужно удалить
        if (!empty($teachersToRemove)) {
            $course->users()->detach($teachersToRemove);
        }

        // Определите идентификаторы новых учителей
        $newTeacherIds = array_diff($teacherIds, $currentTeacherIds);

        // Добавьте новых учителей
        if (!empty($newTeacherIds)) {
            $newTeachers = User::whereIn('id', $newTeacherIds)->get();
            $course->users()->syncWithoutDetaching($newTeachers);
        }

        return response()->json(['message' => 'Teachers updated successfully.'], 200);
    }



    public function getTeacherByCourse(Course $course)
    {
        // Загружаем учителей для данного курса
        $course->load('teachers');

        return response()->json($course);
    }

    public function getCourseBuyers(Course $course)
    {
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        // Получите список пользователей, купивших этот курс
        $buyers = $course->users;

        return response()->json(['buyers' => $buyers], 200);
    }
}
