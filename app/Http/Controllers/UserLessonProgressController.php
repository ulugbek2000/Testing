<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\UserLessonsProgress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLessonProgressController extends Controller
{
    function watched(Lesson $lesson) {
        $user = Auth::user();

    $user->addProgressCourse($lesson);
    }


    function getProgress(Course $course) {
        $user = Auth::user();
        $completedLessons = UserLessonsProgress::where('user_id', $user->id)->where('course_id', $course->id)->where('completed', true)->count();
        $totalLessons = $course->lessons()->count();
        $progressPercentage = $totalLessons > 0 ? ($completedLessons * 100 / $totalLessons) : 0;
        return response()->json([
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'progress_percentage' => $progressPercentage,
        ]);
    }

    public function getWeeklyActivityComparison()
    {
        $user = Auth::user();
        $today = Carbon::now();
        $startOfWeek = $today->startOfWeek();
        $startOfLastWeek = $startOfWeek->subWeek();

        $hoursSpentThisWeek = UserLessonsProgress::where('user', $user)
            ->where('created_at', '>=', $startOfWeek)
            ->sum('hours_spent');

        $hoursSpentLastWeek = UserLessonsProgress::where('user', $user)
            ->where('created_at', '>=', $startOfLastWeek)
            ->where('created_at', '<', $startOfWeek)
            ->sum('hours_spent');

        $comparison = $hoursSpentThisWeek > $hoursSpentLastWeek
            ? 'Активность увеличилась'
            : ($hoursSpentThisWeek < $hoursSpentLastWeek
                ? 'Активность уменьшилась'
                : 'Активность осталась примерно на том же уровне');

        return response()->json([
            'hours_spent_this_week' => $hoursSpentThisWeek,
            'hours_spent_last_week' => $hoursSpentLastWeek,
            'comparison' => $comparison,
        ]);
    }
}
