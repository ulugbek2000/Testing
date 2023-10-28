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
        $purchasedCourses = $user->purchases->groupBy('course_id')->map(function ($purchases) use ($user) {
            $latestPurchase = $purchases->sortByDesc('created_at')->first();
            $course = Course::find($latestPurchase->course_id);
    
            $totalLessons = 0;
            $completedLessons = 0;
    
            foreach ($course->topics as $topic) {
                foreach ($topic->lessons as $lesson) {
                    // Проверьте прогресс пользователя для этого урока
                    $lessonProgress = UserLessonsProgress::where('user_id', $user->id)
                        ->where('lesson_id', $lesson->id)
                        ->first();
    
                    if ($lessonProgress && $lessonProgress->completed) {
                        $completedLessons++;
                    }
    
                    $totalLessons++;
                }
            }
    
            $remainingLessons = $totalLessons - $completedLessons;
            $progressPercentage = $totalLessons === 0 ? 0 : ($completedLessons / $totalLessons) * 100;
    
            return [
                'course' => [
                    'id' => $course->id,
                    'name' => $course->name,
                    'subscription_id' => $latestPurchase->subscription->id,
                    'subscription_name' => $latestPurchase->subscription->name,
                    'subscription_price' => $latestPurchase->subscription->price,
                ],
                'progress_data' => [
                    'completed_lessons' => $completedLessons,
                    'remaining_lessons' => $remainingLessons,
                    'progress_percentage' => $progressPercentage,
                ],
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
