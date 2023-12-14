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
        $daysOfWeek = [1 => 'Понедельник',2 => 'Вторник',3=> 'Среда',4=> 'Четверг',5=> 'Пятница',6=> 'Суббота',7=> 'Воскрасенье']; // Числовые представления дней недели
    
        foreach ($daysOfWeek as $day) {
            $dayStart = $currentWeekStart->copy()->startOfDay()->addDays($day);
            $dayEnd = $dayStart->copy()->endOfDay();
        
            // Найдем все записи прогресса для пользователя в пределах конкретного дня
            $watchedInDay = $userProgress->filter(function ($progress) use ($dayStart, $dayEnd) {
                $completed = (int)$progress->completed;
                $progressDate = Carbon::parse($progress->created_at);
        
                return $completed === 1 && $progressDate->between($dayStart, $dayEnd);
            });
        
            $lessonIds = $watchedInDay->pluck('lesson_id')->toArray();
        // dd($lessonIds);
            // Если есть просмотренные уроки в текущий день, вычисляем общую продолжительность
            if (!empty($lessonIds)) {
                $videos = Media::whereIn('model_id', $lessonIds)->get();

                // dd($videos->toArray());

                // Вычисляем общую продолжительность просмотра видео
                $totalMinutesWatched = $videos->sum(function ($video) {
                    $customProperties = $video->custom_properties;
                
                    // Проверим, есть ли информация о продолжительности
                    if (is_array($customProperties) && isset($customProperties['duration'])) {
                        return (float)$customProperties['duration'];
                    }
                
                    return 0;
                });
        
                // Записываем результат для текущего дня
                $results[] = [
                    'day' => $day,
                    'total_minutes_watched' => $totalMinutesWatched,
                ];
            } else {
                // Если уроки не просматривались, записываем 0 для текущего дня
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
