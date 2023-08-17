<?php

namespace App\Http\Controllers;

use App\Enums\LessonType;
use App\Models\Lesson;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // All Lesons
        $lessons = Lesson::all();
        // Return Json Response
        return response()->json([
            'lessons' => $lessons
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
            //Create Lesson
            $request->validate([
                'topic_id' => 'required',
                'name' => 'required|string',
                'content' => 'required|mimes:mp4,mov,avi,mpeg,mkv,doc'
            ]);


            if (in_array($request->type, [LessonType::Video, LessonType::Audio]) && $request->hasFile('content')) {
                // Upload and store new video file
                $filePath = $request->file('content')->store('lessonContent');
            }

            $data = [
                'topic_id' => $request->topic_id,
                'name' => $request->name,
                'content' => in_array($request->type, [LessonType::Video, LessonType::Audio]) ? $filePath : $request->content,
                'type' => $request->type,
            ];

            Lesson::create($data);

            return response()->json([
                'message' => "Lesson succefully created."
            ], 200);
        } catch (\Exception $e) {
            /*Return response Json */
            return response()->json([
                'message' => $e,
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        // Return Json Response
        return response()->json([
            'lessons' => $lesson
        ], 200);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lesson $lesson)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lesson $lesson)
    {
        $validator = Validator::make($request->all(), [
            'topic_id' => 'integer',
            'name' => 'string',
            'type' => 'mimes:mp4,mov,avi,mpeg,mkv,doc',
        ]);
        $data = [
            $lesson->topic_id = $request->topic_id,
            $lesson->name = $request->name,
            $lesson->update(['type' => 'type']),
        ];
        $lesson->save($data);
        //Return Json Response
        return response()->json([
            'message' => "Lesson succefully updated."
        ], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        $lesson->delete();
        return response()->json([
            'message' => "Lesson succefully deleted."
        ], 200);
    }
}
