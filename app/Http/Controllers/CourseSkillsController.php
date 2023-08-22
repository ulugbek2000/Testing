<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSkills;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseSkillsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Course $course)
    {
        $skills = $course->skills;
        return response()->json($skills);
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
            'name' => 'required|string',
            'icon' => 'required|image|mimes:jpeg,png,jpg,gif,mov',
            'description' => 'required|string|max:255'
        ]);
        $icon = $request->file('icon')->store('icon', 'public');
        try {
            $data = [
                'id' => $request->id,
                'name' => $request->name,
                'icon' => Storage::url($icon),
                'course_id' => $request->course_id,
                'description' => $request->description,
            ];
            CourseSkills::create($data);
            return response()->json([
                'message' => "Topic succefully created."
            ], 200);
        } catch (\Exception $e) {
            //Return response Json
            return response()->json([], $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
