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
    function watched(Lesson $lesson)
    {
        $user = Auth::user();

        $user->addProgressCourse($lesson);
    }


    function getProgress(Course $course)
    {
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



    public function showActivity()
    {
        $user = Auth::user();
        $watchedLessons = $user->courses->flatMap(function ($course) {
            return $course->topics->flatMap(function ($topic) {
                return $topic->lessons;
            });
        });

        $currentWeekStart = Carbon::now()->startOfWeek();
        $results = [];

        for ($i = Carbon::MONDAY; $i <= Carbon::SUNDAY; $i++) {
            $dayStart = $currentWeekStart->copy()->day($i);

            $watchedInDay = $watchedLessons->filter(function ($lesson) use ($dayStart) {
                return Carbon::parse($lesson->created_at)->isSameDay($dayStart);
            });

            $totalMinutesWatched = $watchedInDay->sum('duration');

            $results[$dayStart->format('l')] = $totalMinutesWatched;
        }

        return response()->json($results);
    }
}
