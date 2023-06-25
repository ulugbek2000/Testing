<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;

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

            $data = [
                'topic_id' => $request->topic_id,
                'name' => $request->name,
                'duration' => $request->duration,
                'type' => $request->type
            ];

            Lesson::create($data);

            return response()->json([
                'message' => "Lesson succefully created."
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
    public function show(string $id)
    {
        //Lesson detail
        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json([
                'message' => 'Lesson not found.'
            ], 404);
        }
        // Return Json Response
        return response()->json([
            'lessons' => $lesson
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
    public function update(Request $request, string $id)
    {
        try {
            //find course
            $lesson = Lesson::find($id);
            if (!$lesson) {
                return response()->json([
                    'message' => 'Lesson not found!!'
                ], 404);
            }
            $data = [
                $lesson->topic_id = $request->topic_id,
                $lesson->name = $request->name,
                $lesson->duration = $request->duration,
                $lesson->type = $request->type,
            ];
            $lesson->save($data);
            //Return Json Response
            return response()->json([
                'message' => "Lesson succefully updated."
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
        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json([
                'message' => 'Lesson not found.'
            ], 404);
        }
        $lesson->delete();
        return response()->json([
            'message' => "Lesson succefully deleted."
        ], 200);
    }
}
