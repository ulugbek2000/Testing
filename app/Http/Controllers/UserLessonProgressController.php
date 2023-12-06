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
       //...

$results = [];

for ($i = Carbon::MONDAY; $i <= Carbon::SUNDAY; $i++) {
    $dayStart = $currentWeekStart->copy()->day($i);

    // Выводим информацию об итерации для отладки
    echo "Processing day: {$dayStart->format('l')} ({$dayStart->format('Y-m-d')})\n";

    // Фильтруем по дате прогресса
    $watchedInDay = $userProgress->filter(function ($progress) use ($dayStart) {
        return Carbon::parse($progress->created_at)->isSameDay($dayStart);
    });

    // Выводим информацию о прогрессе за день для отладки
    echo "Progress for the day: ";
    print_r($watchedInDay->toArray());

    // Получаем lesson_id для просмотренных уроков в этот день
    $lessonIds = $watchedInDay->pluck('lesson_id')->toArray();

    // Выводим lessonIds для отладки
    echo "Lesson IDs for the day: " . implode(', ', $lessonIds) . "\n";

    // Получаем общую продолжительность просмотренных уроков в этот день
    $totalMinutesWatched = Lesson::whereIn('id', $lessonIds)->sum('duration');

    // Выводим totalMinutesWatched для отладки
    echo "Total minutes watched for the day: $totalMinutesWatched\n";

    // Добавляем общую продолжительность в результаты
    $results[$dayStart->format('l')] = $totalMinutesWatched;

    echo "Processed day: {$dayStart->format('l')} ({$dayStart->format('Y-m-d')})\n\n";
}

// Добавим дополнительный вывод для отладки
dd([
    'current_week_start' => $currentWeekStart,
    'user_progress' => $userProgress,
    'results' => $results,
]);

//...


        return response()->json($results);
    }
}
