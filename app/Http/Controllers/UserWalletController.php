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
    public function deposit(Request $request)
    {
        $user = Auth::user();
        $newWallet = $request->input('wallet');

        if ($newWallet <= 0) {
            return response()->json(['error' => 'Invalid amount'], 400);
        }

        // Получаем объект баланса пользователя
        $userWallet = $user->wallet;

        // Проверяем, существует ли объект баланса
        if (!$userWallet) {
            // Если объект баланса отсутствует, создаем новый
            $userWallet = new UserWallet();
            $userWallet->wallet = 0; // Устанавливаем начальный баланс
            $userWallet->user()->associate($user); // Связываем с пользователем
            $userWallet->save(); // Сохраняем баланс
        }

        // Увеличиваем баланс
        $userWallet->wallet += $newWallet;

        // Сохраняем изменения
        $userWallet->save();

        return response()->json(['success' => 'Balance updated successfully'], 200);
    }

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

        // Получите список курсов, которые пользователь купил, включая информацию о подписке
        $purchasedCourses = $user->courses->filter(function ($course) {
            return $course->subscription();
        })->map(function ($course) {
            $subscription = $course->subscription;

            // Получите информацию о курсе и его подписке
            return [
                'course_id' => $course,
                // 'subscription_id' => $subscription->id,
                // 'subscription_name' => $subscription->name,
                // 'subscription_price' => $subscription->price,
            ];
        });

        return response()->json(['purchases' => $purchasedCourses], 200);
    }
}
