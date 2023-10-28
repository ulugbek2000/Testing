<?php

namespace App\Http\Controllers;

use App\Models\UserLessonsProgress;
use Illuminate\Http\Request;

class UserLessonProgressController extends Controller
{
    public function getCourseProgress(Request $request, $user_id)
    {
        $userProgress = UserLessonsProgress::where('user_id', $user_id)->get();
        $totalLessons = count($userProgress);
        $completedLessons = $userProgress->where('completed', true)->count();

        $progressPercentage = ($completedLessons / $totalLessons) * 100;

        return response()->json([
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'progress_percentage' => $progressPercentage,
        ]);
    }
}
