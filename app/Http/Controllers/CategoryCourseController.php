<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\Request;

class CategoryCourseController extends Controller
{
    public function attachCoursesToCategory(Request $request, Category $category)
    {
        $request->validate([
            'course_id' => 'required',
        ]);

        $category->courses()->sync($request->course_id);

        return response()->json([
            'message' => 'Courses successfully attached to the category.'
        ], 200);
    }
    public function showCoursesByCategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
        ]);
    
        $courses = Course::where('category_id', $request->category_id)->get();
    
        return response()->json($courses);
    }
}
