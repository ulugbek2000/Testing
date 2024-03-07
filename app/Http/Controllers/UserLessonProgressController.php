<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonUser;
use App\Models\Media;
use App\Models\User;
use App\Models\UserLessonsProgress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserLessonProgressController extends Controller
{
    function watched(Lesson $lesson)
    {
        $user = Auth::user();

        $existingAction = LessonUser::where('lesson_id', $lesson->id)
        ->where('user_id', $user->id)
        ->exists();

    if (!$existingAction) {
        // Если пользователь еще не просматривал урок, увеличиваем количество просмотров и создаем запись об этом действии
        $lesson->increment('views');
        LessonUser::create([
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
            'views' => 1,
        ]);
        return response()->json(['message' => 'Lesson viewed successfully']);
    }

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
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($daysOfWeek as $day) {
            $dayStart = $currentWeekStart->copy()->startOfDay();

            // Ищем следующий день недели
            while ($dayStart->format('l') !== $day) {
                $dayStart->addDay();
            }

            $dayEnd = $dayStart->copy()->endOfDay();

            $watchedInDay = $userProgress->filter(function ($progress) use ($dayStart, $dayEnd) {
                $completed = (int)$progress->completed;
                $progressDate = Carbon::parse($progress->created_at);

                return $completed === 1 && $progressDate->between($dayStart, $dayEnd);
            });


            $lessonIds = $watchedInDay->pluck('lesson_id')->toArray();
            if (!empty($lessonIds)) {
                $videos = Media::whereIn('model_id', $lessonIds)->get();
// dd($videos);
                $totalMinutesWatched = $videos->sum(function ($video) {
                    $customProperties = $video->custom_properties;

                    if (is_array($customProperties) && isset($customProperties['duration'])) {
                        return (float)$customProperties['duration'];
                    }

                    return 0;
                });

                $results[] = [
                    'day' => $day,
                    'total_minutes_watched' => $totalMinutesWatched,
                ];
            } else {
                $results[] = [
                    'day' => $day,
                    'total_minutes_watched' => 0,
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
