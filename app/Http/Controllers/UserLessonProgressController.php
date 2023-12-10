<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
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

    foreach (Carbon::getDays() as $day) {
        $results[] = [
            'day' => $day,
            'total_minutes_watched' => 0,
            'date_range' => '',
        ];
    }
    $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    foreach ($daysOfWeek as $day) {
        $dayStart = $currentWeekStart->copy()->day($day);
        $dayEnd = $dayStart->copy()->endOfDay();

        $watchedInDay = $userProgress->filter(function ($progress) use ($dayStart, $dayEnd) {
            return Carbon::parse($progress->created_at)->between($dayStart, $dayEnd);
        });

        $lessonIds = $watchedInDay->pluck('lesson_id')->toArray();

        $totalMinutesWatched = Lesson::whereIn('id', $lessonIds)->sum(function ($lesson) {
            // Получаем первый медиафайл из коллекции 'content'
            $media = $lesson->getFirstMedia('content');

            // Получаем длительность из пользовательского свойства медиа
            return optional($media)->getCustomProperty('duration') ?? 0;
        });

        $found = false;
        foreach ($results as &$result) {
            if ($result['day'] == $dayStart->format('l')) {
                $result['total_minutes_watched'] = $totalMinutesWatched;
                $result['date_range'] = $dayStart->format('Y.m.d') . ' - ' . $dayEnd->format('Y.m.d');
                $found = true;
                break;
            }
        }

        if (!$found) {
            $results[] = [
                'day' => $dayStart->format('l'),
                'total_minutes_watched' => $totalMinutesWatched,
                'date_range' => $dayStart->format('Y.m.d') . ' - ' . $dayEnd->format('Y.m.d'),
            ];
        }
    }

    $weekStartDate = $currentWeekStart->format('Y.m.d');
    $weekEndDate = $currentWeekStart->copy()->endOfWeek()->format('Y.m.d');
    $results[] = [
        'date_range' => $weekStartDate . ' - ' . $weekEndDate,
    ];

    return response()->json($results);
}
}
