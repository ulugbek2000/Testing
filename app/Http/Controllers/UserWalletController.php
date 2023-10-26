<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSubscription;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserCourse;
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
        $purchasedCourses = $user->purchases->map(function ($purchase) {
            $course = $purchase->course;
            $subscription = $purchase->subscription;

            return [
                'course' => $course,
                'latest_subscrition_id'=>$course->latest_subscrition_id,
                // 'subscription_id' => $subscription->id,
                // 'subscription_name' => $subscription->name,
                // 'subscription_price' => $subscription->price,
            ];
        });
        return response()->json(['purchases' => $purchasedCourses], 200);
    }
}
