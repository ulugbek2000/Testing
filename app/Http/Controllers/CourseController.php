<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;
use app\Http\Requests\CourseStoreRequest;
use App\Http\Resources\CourseResource;
use App\Models\User;
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
            'logo' => 'required|image',
            'name' => 'required|string',
            'slug' => 'required|string|max:255',
            'quantity_lessons' => 'required',
            'hours_lessons' => 'required',
            'short_description' => 'required|max:255',
            // 'duration' => 'required|integer',
            // 'duration_type' => 'required',
            'video' => 'required|mimes:mp4,mov,avi,mpeg,mkv',
            // 'price' => 'required',
        ]);
        $logo = $request->file('logo')->store('images', 'public');
        $video = $request->file('video')->store('videos', 'public'); // Сохранение видео в папку storage/app/public/videos
        try {
            $data = [
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
                // 'price' => $request->price,
            ];
            Course::create($data);
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
        //Course detail
        // $course = Course::find($id);
        // if (!$course) {
        //     return response()->json([
        //         'message' => 'Course not found.'
        //     ], 404);
        // }
        // // Return Json Response
        // return response()->json([
        //     'courses' => $course
        // ], 200);

        return new CourseResource($course);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, \Exception $e)
    {
        // $request->validate
       $validateData
        =  $request->validate([
            'name' => 'string|max:255',
            'slug' => 'string|max:255',
            'quantity_lessons' => 'string',
            'hours_lessons' => 'string',
            'short_description' => 'string|max:255',
        ]);
        $course = Course::find($id);
        if ($request->hasFile('logo')) {
            $imagePath = $request->file('logo')->store('storage', 'public');
            $course->logo = $imagePath;
        }
       
        try {
         
            //find course
            // $data = [
 
                //     $course->name = $request->name,
            //     $course->slug = $request->slug,
            //     $course->quantity_lessons = $request->quantity_lessons,
            //     $course->hours_lessons = $request->hours_lessons,
            //     $course->short_description = $request->short_description,
            //     // $course->duration = $request->duration,
            //     // $course->price = $request->price,
            // ];
            
            // dd($data);

            $course->save($data,$course);
            //Return Json Response
            return response()->json([
                'message' => "Course succefully updated."
            ], 200);
        } catch (\Exception $e) {
            //Return Json Response
            return response()->json([
                'message' => $e,
            ], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json([
                'message' => 'Course not found.'
            ], 404);
        }
        $course->delete();
        return response()->json([
            'message' => "Course succefully deleted."
        ], 200);
    }

    //Add student in courses
    public function enrollStudent(Request $request, $courseId, $studentId)
    {
        $course = Course::findOrFail($courseId);
        $student = User::findOrFail($studentId);
        // try {
        //     $data = [
        //         'name' => $request->name,
        //         'email' => $request->email,
        //         'phone' => $request->phone,
        //         'password' => $request->password,
        //     ];
        //     User::create($data);
        //     return response()->json([
        //         'message' => "Student succefully created."
        //     ], 200);
        // } catch (\Exception $e) {
        //     //Return response Json
        //     return response()->json([
        //         'message' => $e,
        //     ], 500);
        // }

        $course->students()->attach($student);

        return redirect()->route('courses.show', $courseId)->with('success', 'Студент успешно записан на курс.');
        //     return response()->json([
        //         'message' => "Student succefully added."
        //     ], 200);
        // }
    }
}
