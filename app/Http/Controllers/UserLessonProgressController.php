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
        $userProgress = UserLessonsProgress::where('user_id', $user->id)->get();

        $currentWeekStart = Carbon::now()->startOfWeek();

        $results = [];

        // Диапазон дат для всей недели
        $weekStartDate = $currentWeekStart->format('d.m.Y');
        $weekEndDate = $currentWeekStart->copy()->endOfWeek()->format('d.m.Y');

        // Инициализация результатов для каждого дня недели
        foreach (Carbon::getDays() as $day) {
            $results[] = [
                'day' => $day,
                'total_minutes_watched' => 0,
            ];
        }

        for ($i = Carbon::MONDAY; $i <= Carbon::SUNDAY; $i++) {
            $dayStart = $currentWeekStart->copy()->day($i);

            $watchedInDay = $userProgress->filter(function ($progress) use ($dayStart) {
                return Carbon::parse($progress->created_at)->isSameDay($dayStart);
            });

            $lessonIds = $watchedInDay->pluck('lesson_id')->toArray();

            $totalMinutesWatched = Lesson::whereIn('id', $lessonIds)->sum('duration');

            // Обновляем результаты для каждого дня недели
            $results[$dayStart->dayOfWeek]['total_minutes_watched'] = $totalMinutesWatched;
            $results[$dayStart->dayOfWeek]['date_range'] = $dayStart->format('d.m.Y') . ' - ' . $dayStart->copy()->endOfDay()->format('d.m.Y');
        }

        // Добавляем информацию о диапазоне дат для всей недели
        $results[] = [
            'date_range' => $weekStartDate . ' - ' . $weekEndDate,
        ];

        return response()->json($results);
    }
}
