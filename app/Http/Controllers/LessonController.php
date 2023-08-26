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
use Symfony\Contracts\Service\Attribute\Required;
use Nette\Utils\Random;
use Carbon\Carbon;

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
        $request->validate([
            'topic_id' => 'integer',
            'name' => 'string',
            'cover' => 'image|mimes:jpeg,png,jpg,gif,mov',
            'duration' => 'string|nullable'
        ]);
        // dd()->response()->json($request);
        $type = $request->input('type');
        $сontent = $request->input('content');
        $cover = $request->file('cover')->store('cover', 'public');

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
            'cover' => Storage::url($cover),
            'duration' => $request->duration,
            'content' => in_array($request->type, [LessonTypes::Video, LessonTypes::Audio]) ? $filePath : $request->content,
            'type' => $request->type,
        ];

        Lesson::create($data);

        return response()->json(['message' => 'Lesson created successfully.']);
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
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,mov',
            'duration' => 'string|nullable',
            'content' => 'nullable',
            // 'type' =>  [new Enum(LessonTypes::class)]
        ]);

        $coverpath = $lesson->cover;
        $content = $lesson->content;

        if ($request->hasFile('cover')) {
            // Delete old cover file if needed
            Storage::delete($lesson->cover);
            // Upload and store new cover file
            $coverpath = $request->file('cover')->store('cover', 'public');
        }
        if ($request->type == LessonTypes::Video ||  $request->type == LessonTypes::Audio) {
            if ($request->hasFile('content')) {
                // Delete old cover file if needed
                Storage::delete($lesson->content);
                // Upload and store new cover file
                $content = $request->file('content')->store('content', 'public');
            }
        } else {
            $content = $request->content;
        }

        $data = array_merge($request->only(['name', 'type', 'topic_id', 'duration']), [
            'cover' => $coverpath,
            'content' => $content
        ]);

        $lesson->update($data);

        // return response()->json($data);

        return response()->json(['message' => 'Lesson updated successfully']);
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
