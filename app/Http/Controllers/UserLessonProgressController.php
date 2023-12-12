<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
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
    
        $currentWeekStart = Carbon::now()->startOfWeek();
        $currentWeekEnd = Carbon::now()->endOfWeek();
    
        // Получить список просмотренных уроков за неделю
        $watchedLessons = UserLessonsProgress::where('user_id', $user->id)
            ->whereDate('created_at', '>=', $currentWeekStart)
            ->whereDate('created_at', '<=', $currentWeekEnd)
            ->get();
    
        // Сгруппировать уроки по дням недели
        $watchedLessonsByDay = $watchedLessons->groupBy(function ($lesson) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $lesson->created_at)->dayOfWeek;
        });
    
        // Получить общее количество просмотренных минут за каждый день недели
        $results = [];
        foreach ($watchedLessonsByDay as $day => $lessons) {
            $results[] = [
                'day' => $day,
                'total_minutes_watched' => $lessons->sum(function ($lesson) {
                    return $lesson->media->duration / 60;
                }),
            ];
        }
    
        // Добавить данные недели
        $results[] = [
            'date_range' => $currentWeekStart->format('Y.m.d') . ' - ' . $currentWeekEnd->format('Y.m.d'),
            'total_minutes_watched' => $watchedLessons->sum(function ($lesson) {
                return $lesson->media->custom_properties;
            }),
        ];
    
        return response()->json($results);
    }
    
}
