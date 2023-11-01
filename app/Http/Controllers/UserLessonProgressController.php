<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\UserLessonsProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLessonProgressController extends Controller
{
    function watched(Lesson $lesson) {
        $user = Auth::user();

    $user->addProgress($lesson);
    }


    function getProgress(Course $course) {
        $user = Auth::user();
        $completedLessons = UserLessonsProgress::where('user_id', $user->id)->where('course_id', $course->id)->where('completed', true)->count();
        $totalLessons = $course->topics()->with(['lessons'=> function($l){
            return $l->count();
        }])->count();

        dd($totalLessons);
        $progressPercentage = ($completedLessons / $totalLessons) * 100;
    }

    public function getCourseProgress(Request $request)
    {
        $user_id = Auth::user();
        $userProgress = UserLessonsProgress::where('user_id', $user_id)->get();
        $totalLessons = count($userProgress);
        $completedLessons = $userProgress->where('completed', true)->count();

        if ($totalLessons === 0) {
            $progressPercentage = 0; // Set progress to 0 if there are no lessons.
        } else {
            $progressPercentage = ($completedLessons / $totalLessons) * 100;
        }
        return response()->json([
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'progress_percentage' => $progressPercentage,
        ]);
    }
}
