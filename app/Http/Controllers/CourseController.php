<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;
use app\Http\Requests\CourseStoreRequest;
use App\Http\Resources\CourseResource;
use App\Models\Category;
use App\Models\CourseSkills;
use App\Models\CourseSubscription;
use App\Models\User;
use App\Models\UserCourse;
use App\Rules\FileOrString;
use Carbon\Carbon;
use FFMpeg\FFProbe;
use getID3;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Nette\Utils\Random;
use Symfony\Contracts\Service\Attribute\Required;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        // Параметры для пагинации и поиска
        $perPage = $request->input('per_page', 12);
        $search = $request->input('search');

        // Запрос на получение курсов
        $query = Course::query();

        // Если есть параметр для поиска, добавляем условия поиска
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }

        // Добавляем сортировку по дате создания, чтобы показать последний созданный курс
        $query->latest();

        // Получаем курсы с пагинацией
        return  CourseResource::collection($query->paginate($perPage));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Category $category)
    {
        $request->validate([
            'logo' => ['nullable', new FileOrString],
            'name' => 'required|string',
            'slug' => 'nullable|string|max:255',
            'short_description' => 'required',
            'video' => ['nullable', new FileOrString],
            'category_id' => 'required|exists:categories,id',
            'has_certificate' => 'required|boolean',
        ]);

        $logo = $request->file('logo')->store('images', 'public');
        $video = $request->file('video')->store('videos', 'public');

        try {
            $course = Course::create([
                'logo' => Storage::url($logo),
                'name' => $request->name,
                'slug' => $request->slug,
                'short_description' => $request->short_description,
                'has_certificate' => $request->has_certificate,
                'category_id' => $request->category_id,
            ]);

            // Check if the 'video' file exists in the request
            if ($request->hasFile('video')) {
                // Add media from request
                $media =  $course->addMediaFromRequest('video')->toMediaCollection('videos');

                // Use getID3 to get video duration
                $getID3 = new getID3();
                $fileInfo = $getID3->analyze($media->getPath());

                $durationInSeconds = $fileInfo['playtime_seconds'];

                $media->setCustomProperty('duration', $durationInSeconds)->save();
                $videoPath = $media->getPath();
                $storagePath = substr($videoPath, strpos($videoPath, '/storage'));
                $course->video = $storagePath;
                $course->save();
            }

            return response()->json([
                'message' => "Course successfully created.",
            ], 200);
        } catch (\Exception $e) {
            // Return response Json
            return response()->json([
                'message' => $e->getMessage(),
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'short_description' => 'required|string',
            'logo' => ['nullable', new FileOrString],
            'video' => ['nullable', new FileOrString],
            'category_id' => 'exists:categories,id',
        ]);

        // Обработка логотипа
        if ($request->hasFile('logo')) {
            Storage::delete($course->logo);
            $logopath = $request->file('logo')->store('public/images');
            $logopath = 'storage/' . substr($logopath, strpos($logopath, 'public/') + 7);
        } else {
            $logopath = $course->logo;
        }

        // Обработка видео

        if ($request->hasFile('video')) {
            Storage::delete($course->video);
            $media = $course->addMediaFromRequest('video')->toMediaCollection('videos');

            // Получение длительности видео
            $getID3 = new getID3();
            $fileInfo = $getID3->analyze($media->getPath());
            $durationInSeconds = $fileInfo['playtime_seconds'];

            $media->setCustomProperty('duration', $durationInSeconds)->save();

            $videoPath = $media->getPath();
            $storagePath = substr($videoPath, strpos($videoPath, '/storage'));
            $path = $course->video = $storagePath;
        } else {
            $path = $course->video;
        }

        $data = array_merge($request->only(['name', 'slug', 'short_description', 'category_id']), [
            'logo' => $logopath,
            'video' => $path,
        ]);

        $course->update($data);

        return response()->json(['message' => 'Course updated successfully'], 200);
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

        $currentTeacherIds = $course->users()
            ->whereHas('roles', function ($query) {
                $query->where('id', UserType::Teacher);
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

    public function getCoursesByCategory(Category $category)
    {
        $categoryWithCourses = $category->courses;

        if (!$categoryWithCourses) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        return response()->json(['data' => $categoryWithCourses]);
    }

    public function hideCourse(Course $course)
    {
        if (Auth::user()->hasRole(UserType::Admin)) {
            // Инвертируем значение поля is_hidden
            $course->update(['is_hidden' => !$course->is_hidden]);

            // Определяем текстовое сообщение в зависимости от нового состояния
            $message = $course->is_hidden ? 'Курс успешно скрыт' : 'Курс успешно отображен';

            return response()->json(['message' => $message], 200);
        }
    }
}
