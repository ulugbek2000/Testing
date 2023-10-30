<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSubscription;
use App\Models\Lesson;
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
        $purchasedCoursesData = $user->purchases->groupBy('course_id')->map(function ($purchases) use ($user) {
            $latestPurchase = $purchases->sortByDesc('created_at')->first();
            $course = Course::find($latestPurchase->course_id);
            // dd($course, $latestPurchase);
            $totalLessons = 0;
            $completedLessons = 0;

            // Получите уроки, связанные с темами этого курса
            $lessons = Lesson::whereIn('topic_id', $course->topics->pluck('id'))->get();

            // Здесь вы можете выполнить проверку прогресса пользователя для каждого урока
            foreach ($lessons as $lesson) {
                $lessonProgress = UserLessonsProgress::where('user_id', $user->id)
                    ->where('lesson_id', $lesson->id)
                    ->first();

                if ($lessonProgress && $lessonProgress->completed) {
                    $completedLessons++;
                }
                $totalLessons++;
            }

            $remainingLessons = $totalLessons - $completedLessons;
            $progressPercentage = $totalLessons === 0 ? 0 : ($completedLessons / $totalLessons) * 100;

            return [
                'course' => [
                    'id' => $course->id,
                    'logo' => $course->logo,
                    'name' => $course->name,
                    'slug' => $course->slug,
                    'quantity_lessons' => $course->quantity_lessons,
                    'hours_lessons' => $course->hours_lessons,
                    'short_description' => $course->short_description,
                    'video' => $course->video,
                    'has_certificate' => $course->has_certificate,
                ],
                'subscription_id' => $latestPurchase->subscription->id,
                'subscription_name' => $latestPurchase->subscription->name,
                'subscription_price' => $latestPurchase->subscription->price,
                'completed_lessons' => $completedLessons,
                'remaining_lessons' => $remainingLessons,
                'progress_percentage' => $progressPercentage,
            ];
        });

        return response()->json(['purchases' => $purchasedCoursesData->values()], 200);
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
