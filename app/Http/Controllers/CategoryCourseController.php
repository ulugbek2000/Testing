<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryCourseController extends Controller
{
    public function attachCoursesToCategory(Request $request, Category $category)
    {
        $request->validate([
            'course_ids' => 'required',
        ]);

        $category->courses()->sync($request->course_ids);

        return response()->json([
            'message' => 'Courses successfully attached to the category.'
        ], 200);
    }
    public function showCoursesByCategory(Category $category)
    {
        $courses = $category->courses;

        return response()->json([
            'category' => $category,
            'courses' => $courses,
        ]);
    }
}
