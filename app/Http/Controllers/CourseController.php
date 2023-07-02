<?php
namespace App\Http\Controllers;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use app\Http\Requests\CourseStoreRequest;
use Nette\Utils\Random;
class CourseController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    /*  */
    public function index()
    {
        // All Courses
        $courses = Course::all();
        // Return Json Response
        return response()->json([
            'courses' => $courses
        ], 200);
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
        try {
            $data = [
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
                'price' => $request->price
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
    public function show($id)
    {
        //Course detail
        $course = Course::find($id);
        if (!$course) {
            return response()->json([
                'message' => 'Course not found.'
            ], 404);
        }
        // Return Json Response
        return response()->json([
            'courses' => $course
        ], 200);
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
    public function update(Request $request, $id)
    {
        try {
            //find course
            $course = Course::find($id);
            if (!$course) {
                return response()->json([
                    'message' => 'Course not found!!'
                ], 404);
            }
            $data = [
                $course->name = $request->name,
                $course->slug = $request->slug,
                $course->description = $request->description,
                $course->price = $request->price,
            ];
            $course->save($data);
            //Return Json Response
            return response()->json([
                'message' => "course succefully updated."
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
}
