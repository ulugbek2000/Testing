<?php

namespace App\Http\Controllers;

use App\Enums\LessonType;
use App\Enums\LessonTypes;
use App\Models\Lesson;
use App\Models\Topic;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Enum;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Topic $topic)
    {
        $lessons = $topic->lessons;
        return response()->json($lessons);
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
    public function store(Request $request, Lesson $lesson)
    {
        // try {
        //Create Lesson
        // $request->validate([
        //     'topic_id' => 'required|integer',
        //     'name' => 'required|string',
        //     // 'cover' => 'image|mimes:jpeg,png,jpg,gif,mov',
        //     'content' => 'mimes:mp4,mov,avi,mpeg,mkv,doc'
        // ]);


        //     $cover = $request->file('cover')->store('cover', 'public');

        //     if (in_array($request->type, [LessonTypes::Video, LessonTypes::Audio]) && $request->hasFile('content')) {
        //         // Upload and store new video file
        //         $filePath = $request->file('content')->store('lessonContent');
        //     }
        //     if (!$filePath) {
        //         $newLessonType = LessonTypes::Text; // Это значение будет "text"
        //         $lesson->type = $newLessonType;
        //         $lesson->save();
        //     }

        //     $data = [
        //         'topic_id' => $request->topic_id,
        //         'name' => $request->name,
        //         'cover' => Storage::url($cover),
        //         'content' => in_array($request->type, [LessonTypes::Video, LessonTypes::Audio]) ? $filePath : $request->content,
        //         'type' => $request->type,
        //     ];

        //     Lesson::create($data);

        //     return response()->json([
        //         'message' => "Lesson succefully created."
        //     ], 200);
        // } catch (\Exception $e) {
        //     /*Return response Json */
        //     return response()->json([
        //         'message' => $e,
        //     ], 500);
        // }

        $type = $request->input('type');
        $сontent = $request->input('content');
        
        $lesson = new Lesson();
        $lesson->type = $type;
            
        if ($type === 'text') {
            $lesson->content = $сontent;
        } elseif ($type === 'video' || $type === 'audio') {
            $filePath = $request->file('content')->store('lessonContent');
        }
        $data = [
                    'topic_id' => $request->topic_id,
                    'name' => $request->name,
                    // 'cover' => Storage::url($cover),
                    'content' => in_array($request->type, [LessonTypes::Video, LessonTypes::Audio]) ? $filePath : $request->content,
                    'type' => $request->type,
                ];
        Lesson::create($data);

        return response()->json('Lesson created successfully.');
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
        $request->validate([
            'topic_id' => 'integer',
            'name' => 'string',
            'cover' => 'image|mimes:jpeg,png,jpg,gif,mov',
            'content' => 'mimes:mp4,mov,avi,mpeg,mkv,doc'
        ]);

        if ($request->hasFile('cover')) {
            // Delete old cover file if needed
            Storage::delete($lesson->cover);
            // Upload and store new cover file
            $coverpath = $request->file('cover')->store('images');
        }
        $data = array_merge($request->only(['name', 'content', 'topic_id', 'lesson_types']), [
            'cover' => $coverpath,
        ]);
        $lesson->update($data);
        return response()->json(['message' => 'Course updated successfuly'], 200);
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
