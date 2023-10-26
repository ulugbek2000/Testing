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
        $purchasedCourses = $user->purchases->groupBy('course_id')->map(function ($purchases) {
            $latestPurchase = $purchases->sortByDesc('created_at')->first();
            $course = $latestPurchase->course;
    
            return [
                'course' => $course,
                'subscription_id' => $latestPurchase->subscription->id,
                'subscription_name' => $latestPurchase->subscription->name,
                'subscription_price' => $latestPurchase->subscription->price,
            ];
        });
    
        return response()->json(['purchases' => $purchasedCourses->values()], 200);
    }
    
}
