<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
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
    public function getBalance(User $user)
    {
        // Получаем текущего пользователя
        $loggedInUser = Auth::user();

        // Проверяем авторизацию
        if (!$loggedInUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Проверяем, является ли текущий пользователь администратором
        if ($loggedInUser->hasRole(UserType::Admin)) {
            // Если пользователь администратор, получаем ID пользователя из параметра метода
            $userId = $user->id;

            // Находим кошелек пользователя
            $userWallet = UserWallet::where('user_id', $userId)->first();

            // Проверяем, найден ли кошелек
            if (!$userWallet) {
                return response()->json(['balance' => 0], 200);
            } else {
                return response()->json(['balance' => $userWallet->wallet], 200);
            }
        } else {
            // Если пользователь не администратор, проверяем, является ли пользователь текущим пользователем
            if ($loggedInUser->hasRole(UserType::Student)) {
                // Получаем кошелек текущего пользователя
                $userWallet = $loggedInUser->wallet;

                // Проверяем, найден ли кошелек
                if (!$userWallet) {
                    return response()->json(['balance' => 0], 200);
                } else {
                    return response()->json(['balance' => $userWallet->wallet], 200);
                }
            }
        }
    }

    public function getMyPurchases()
    {
        $user = Auth::user();

        // Получите список покупок пользователя, включая информацию о курсах и их подписках
        $purchasedCoursesData = $user->subscriptions->groupBy('course_id')->map(function ($purchases) use ($user) {
            $latestPurchase = $purchases->sortByDesc('created_at')->first();
            $course = Course::find($latestPurchase->course_id);

            if ($latestPurchase->deleted_at && $latestPurchase->deleted_at < now()) {
                return null; // Если подписка истекла, вернем null
            }
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

    public function getPurchasesByCourseId($courseId)
    {
        $user = Auth::user();

        $latestPurchase = $user->subscriptions()
            ->where('course_id', $courseId)
            ->latest()
            ->first();

        if ($latestPurchase) {
            $courseInfo = $latestPurchase->course;

            if ($latestPurchase->deleted_at < now()) {
                return response()->json(['message' => 'Подписка истекла'], 403);
            }

            $subscription = Subscription::find($latestPurchase->subscription_id);
            $subscriptionName = $subscription->name;

            $course = Course::find($latestPurchase->course_id);

            $totalLessons = 0;
            $completedLessons = 0;

            $completedLessons = UserLessonsProgress::where('user_id', $user->id)->where('course_id', $course->id)->where('completed', true)->count();
            $totalLessons = $course->lessons()->count();
            $progressPercentage = $totalLessons > 0 ? ($completedLessons * 100 / $totalLessons) : 0;

            $latestSubscription = $user->subscriptions->where('course_id', $course->id)->sortByDesc('created_at')->first();
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
                        'deleted_at' => $latestSubscription->deleted_at,
                    ],
                ],
            ];

            return response()->json($purchasesInfo, 200);
        } else {
            return response()->json(['message' => 'Покупка не найдена'], 404);
        }
    }
}
