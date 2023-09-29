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
        $courseSkills = $course->courseSkills;
        return response()->json($courseSkills);
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
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,mov',
            'description' => 'required|string|max:255',
            'course_id' => 'required|integer',
        ]);
        $icon = $request->file('icon')->store('icon', 'public');
        try {
            $data = [
                // 'id' => $request->id,
                'name' => $request->name,
                'icon' => Storage::url($icon),
                'course_id' => $request->course_id,
                'description' => $request->description,
            ];
            CourseSkills::create($data);
            return response()->json([
                'message' => "Skill course succefully created."
            ], 200);
        } catch (\Exception $e) {
            //Return response Json
            return response()->json([], $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CourseSkills $course)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CourseSkills $course)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CourseSkills $courseSkills)
    {
        $request->validate([
            'name' => 'string',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,mov',
            'description' => 'string|max:255',
            'course_id' => 'required|integer',
        ]);
        if ($request->hasFile('icon')) {
            // Delete old icon file if needed
           if (Storage::exists($courseSkills->icon)) {
            Storage::delete($courseSkills->icon);
           } 
            
            // Upload and store new icon file
            $iconpath = $request->file('icon')->store('icon', 'public');
        } else {
            $iconpath = $courseSkills->icon;
        }
        $data = array_merge($request->only(['name', 'description', 'course_id']), [
            'icon' => $iconpath,
        ]);
        $courseSkills->update($data);
        return response()->json(['message' => 'Skill updated successfuly'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourseSkills $courseSkills)
    {
        $courseSkills->delete();
        return response()->json([
            'message' => "Skill successfuly deleted."
        ], 200);
    }
}
