<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\Course;
use App\Models\Topic;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // public function ind(Course $course)
    // {
    //     $topics = $course->topics;
    //     return response()->json($topics);
    // }


    public function index(Course $course)
    {
        $user = Auth::user();
    
        if ($user) {
            $isAdmin = $user->hasRole(UserType::Admin);
            $isStudent = $user->hasRole(UserType::Student);
    
            if ($isAdmin) {
                $lessons = collect();
    
                foreach ($course->topics as $topic) {
                    $lessons = $lessons->merge($topic->lessons);
                }
    
                foreach ($lessons as $lesson) {
                    if ($lesson->hasMedia('content')) {
                        $mediaData = DB::table('media')
                            ->where('model_type', '=', 'App\\Models\\Lesson')
                            ->where('model_id', '=', $lesson->id)
                            ->select('custom_properties')
                            ->get()
                            ->pluck('custom_properties');
                        // Делайте что-то с $mediaData
                    }
                }
                
                return response()->json(['data' => $course->topics]);
            } elseif ($isStudent) {
                return response()->json(['data' => $course->topics]);
            }
        } else {
            // Гость
            return response()->json(['data' => $course->topics]);
        }
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
            'topic_name' => 'string|max:255',
            'course_id' => 'required|integer'
        ]);

        try {
            $data = [
                'course_id' => $request->course_id,
                'topic_name' => $request->topic_name,
            ];
            Topic::create($data);
            return response()->json([
                'message' => "Topic succefully created."
            ], 200);
        } catch (\Exception $e) {
            //Return response Json
            return response()->json([$e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Topic $topic)
    {
        // Return Json Response
        return response()->json([
            'topics' => $topic
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Topic $topic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Topic $topic)
    {

        $request->validate([
            'topic_name' => 'string|max:255',
            'course_id' => 'required|integer'
        ]);

        $topic->update($request->only(['topic_name', 'course_id']));
        //Return Json Response
        return response()->json([
            'message' => "Topic succefully updated."
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Topic $topic)
    {
        $topic->delete();
        return response()->json([
            'message' => "Topic succefully deleted."
        ], 200);
    }
}
