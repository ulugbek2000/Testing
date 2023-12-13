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
        $daysOfWeek = [1, 2, 3, 4, 5, 6, 7]; // Числовые представления дней недели
    
        foreach ($daysOfWeek as $day) {
            $dayStart = $currentWeekStart->copy()->startOfDay()->addDays($day - 1);
            $dayEnd = $dayStart->copy()->endOfDay();
        
            $watchedInDay = $userProgress->filter(function ($progress) use ($dayStart, $dayEnd) {
                $completed = (int)$progress->completed;
                $progressDate = Carbon::parse($progress->created_at);
        
                return $completed === 1 && $progressDate->between($dayStart, $dayEnd);
            });
        
            // Если вы хотите увидеть результат фильтрации для отладки
            // dd($watchedInDay->toArray());
        
            $lessonIds = $watchedInDay->pluck('lesson_id')->toArray();
        
            // Если нет просмотренных уроков, установите $totalMinutesWatched в 0
            $totalMinutesWatched = 0;
        
            if (!empty($lessonIds)) {
                $totalMinutesWatched = Media::whereIn('model_id', $lessonIds)->sum('custom_properties');
            }
        
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
