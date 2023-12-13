<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
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
        $daysOfWeek = ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'];
    
        foreach ($daysOfWeek as $day) {
            $dayStart = $currentWeekStart->copy()->day($day);
            $dayEnd = $dayStart->copy()->endOfDay();
    
            $watchedInDay = $userProgress->filter(function ($progress) use ($dayStart, $dayEnd) {
                return $progress->completed == 1 && Carbon::parse($progress->created_at)->between($dayStart, $dayEnd);
            });
    
            if ($watchedInDay->isEmpty()) {
                // Если прогресса нет, пропускаем этот день
                continue;
            }
            // dd($watchedInDay);
    
            $lessonIds = $watchedInDay->pluck('lesson_id')->toArray();
    
            $totalMinutesWatched = Media::whereIn('model_id', $lessonIds)->sum('custom_properties');
    
            $results[] = [
                'day' => $day,
                'total_minutes_watched' => $totalMinutesWatched,
            ];
        }
    
        $weekStartDate = $currentWeekStart->format('Y.m.d');
        $weekEndDate = $currentWeekStart->copy()->endOfWeek()->format('Y.m.d');
    
        $results[] = [
            'date_range' => $weekStartDate . ' - ' . $weekEndDate,
        ];
    
        return response()->json($results);
    }
    
    
}
