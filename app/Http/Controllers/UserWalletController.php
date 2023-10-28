<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSubscription;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserCourse;
use App\Models\UserLessonsProgress;
use App\Models\UserSubscription;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserWalletController extends Controller
{
    public function getBalance()
    {
        $user = Auth::user();
        $userWallet = $user->wallet;

        if (!$userWallet) {
            // Если объект баланса отсутствует, верните 0 баланс
            $wallet = 0;
        } else {
            $wallet = $userWallet->wallet;
        }

        return response()->json(['wallet' => $wallet], 200);
    }

    public function getMyPurchases()
    {
        $user = Auth::user();
    
        // Получите список покупок пользователя, включая информацию о курсах и их подписках
        $purchasedCourses = $user->purchases->groupBy('course_id')->map(function ($purchases, $course_id) use ($user) {
            $latestPurchase = $purchases->sortByDesc('created_at')->first();
            $course = Course::find($course_id);
    
            // Получите все темы для данного курса
            $topics = $course->topics;
    
            $progressData = [];
    
            foreach ($topics as $topic) {
                // Получите уроки, принадлежащие этой теме
                $lessons = $topic->lessons;
    
                // Определите прогресс для каждого урока внутри темы
                $topicProgress = [];
    
                foreach ($lessons as $lesson) {
                    $userProgress = UserLessonsProgress::where('user_id', $user->id)
                        ->where('lesson_id', $lesson->id)
                        ->first();
    
                    if ($userProgress) {
                        $topicProgress[] = [
                            'lesson_name' => $lesson->name,
                            'completed' => $userProgress->completed,
                        ];
                    }
                }
    
                // Соберите информацию о прогрессе для этой темы
                $completedLessons = count(array_filter($topicProgress, function ($lesson) {
                    return $lesson['completed'];
                }));
                $totalLessons = count($topicProgress);
                $remainingLessons = $totalLessons - $completedLessons;
    
                $progressPercentage = $totalLessons === 0 ? 0 : ($completedLessons / $totalLessons) * 100;
    
                $progressData[] = [
                    'topic_name' => $topic->name,
                    'total_lessons' => $totalLessons,
                    'completed_lessons' => $completedLessons,
                    'remaining_lessons' => $remainingLessons,
                    'progress_percentage' => $progressPercentage,
                ];
            }
    
            return [
                'course' => $course,
                'subscription_id' => $latestPurchase->subscription->id,
                'subscription_name' => $latestPurchase->subscription->name,
                'subscription_price' => $latestPurchase->subscription->price,
                'progress_data' => $progressData,
            ];
        });
    
        return response()->json(['purchases' => $purchasedCourses->values()], 200);
    }
    
    


    public function getPurchasesByCourseId($courseId)
    {
        $user = Auth::user();
    
        $latestPurchase = $user->purchases()
            ->where('course_id', $courseId)
            ->latest()
            ->first();
    
        if ($latestPurchase) {
            $courseInfo = $latestPurchase->course;
    
            $subscription = Subscription::find($latestPurchase->subscription_id);
            $subscriptionName = $subscription->name;
    
            $purchasesInfo = [
                'purchases' => [
                    [
                        'course' => [
                            'id' => $courseInfo->id,
                            'logo' => $courseInfo->logo,
                            'name' => $courseInfo->name,
                            'slug' => $courseInfo->slug,
                            'quantity_lessons' => $courseInfo->quantity_lessons,
                            'hours_lessons' => $courseInfo->hours_lessons,
                            'short_description' => $courseInfo->short_description,
                            'video' => $courseInfo->video,
                            'has_certificate' => $courseInfo->has_certificate,
                        ],
                        'subscription_id' => $latestPurchase->subscription_id,
                        'subscription_price' => $latestPurchase->price,
                        'subscription_name' => $subscriptionName,
                    ],
                ],
            ];
    
            return response()->json($purchasesInfo, 200);
        } else {
            return response()->json(['message' => 'Покупка не найдена'], 404);
        }
    }
    
}
