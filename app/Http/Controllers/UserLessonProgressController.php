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
        $watchedLessons = $user->courses()->topics->lessons;

        // Определить текущую неделю и начало текущего дня
        $currentWeekStart = Carbon::now()->startOfWeek();
        $currentDay = Carbon::now()->startOfDay();

        // Инициализировать массив результатов
        $results = [];

        // Цикл по дням недели от понедельника до воскресенья
        for ($i = 0; $i <= Carbon::SUNDAY; $i++) {
            $dayStart = $currentWeekStart->copy()->addDays($i);

            // Проверить, просматривались ли уроки в этот день
            $watchedInDay = $watchedLessons->filter(function ($lesson) use ($dayStart) {
                return Carbon::parse($lesson->created_at)->isSameDay($dayStart);
            });

            // Рассчитать общую продолжительность видео для этого дня
            $totalMinutesWatched = $watchedInDay->sum('duration');

            // Сформировать результат
            $results[$dayStart->format('l')] = $dayStart->isSameDay($currentDay) ? $totalMinutesWatched : 0;
        }

        return response()->json($results);
    }
}
