<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSubscription;
use App\Models\Lesson;
use App\Models\Subscription;
use App\Models\Topic;
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

            $totalLessons = 0;
            $completedLessons = 0;

            $completedLessons = UserLessonsProgress::where('user_id', $user->id)->where('course_id', $course->id)->where('completed', true)->count();
            $totalLessons = $course->lessons()->count();
            $progressPercentage = $totalLessons > 0 ? ($completedLessons * 100 / $totalLessons) : 0;

            $latestSubscription = $user->subscriptions->where('course_id', $course->id)->sortByDesc('created_at')->first();

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
                'total_lessons' => $totalLessons,
                'progress_percentage' => $progressPercentage,
                'deleted_at' => $latestSubscription->deleted_at,
            ];
        });

        return response()->json(['purchases' => $purchasedCoursesData->values()], 200);
    }

    public function getPurchasesByCourseId(Course $course)
    {
        $user = Auth::user();

        $latestPurchase = $user->purchases()
            ->where('course_id', $course->id)
            ->latest()
            ->first();

        if ($latestPurchase) {
            $courseInfo = $latestPurchase->course;

            $subscription = Subscription::find($latestPurchase->subscription_id);
            $subscriptionName = $subscription->name;

            $totalLessons = 0;
            $completedLessons = 0;

            $completedLessons = UserLessonsProgress::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('completed', true)
                ->count();
            $totalLessons = $course
                ->lessons()
                ->count();
            $progressPercentage = $totalLessons > 0 ? ($completedLessons * 100 / $totalLessons) : 0;
            $userSubscriptions = $user->subscriptions->whereNotNull('deleted_at');


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
                        'completed_lessons' => $completedLessons,
                        'total_lessons' => $totalLessons,
                        'progress_percentage' => $progressPercentage,
                        // 'subscription' => $userSubscriptions
                    ],
                ],
            ];

            return response()->json($purchasesInfo, 200);
        } else {
            return response()->json(['message' => 'Покупка не найдена'], 404);
        }
    }
}
