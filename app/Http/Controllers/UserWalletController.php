<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSubscription;
use App\Models\Subscription;
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
        $newBalance = $request->input('balance');

        if ($newBalance <= 0) {
            return response()->json(['error' => 'Invalid amount'], 400);
        }

        // Получаем объект баланса пользователя
        $userBalance = $user->balance;

        // Проверяем, существует ли объект баланса
        if (!$userBalance) {
            // Если объект баланса отсутствует, создаем новый
            $userBalance = new UserWallet();
            $userBalance->balance = 0; // Устанавливаем начальный баланс
            $userBalance->user()->associate($user); // Связываем с пользователем
            $userBalance->save(); // Сохраняем баланс
        }

        // Увеличиваем баланс
        $userBalance->balance += $newBalance;

        // Сохраняем изменения
        $userBalance->save();

        return response()->json(['success' => 'Balance updated successfully'], 200);
    }

    public function getBalance()
    {
        $user = Auth::user();
        $userBalance = $user->balance;

        if (!$userBalance) {
            // Если объект баланса отсутствует, верните 0 баланс
            $balance = 0;
        } else {
            $balance = $userBalance->balance;
        }

        return response()->json(['balance' => $balance], 200);
    }

    public function purchaseCourse(Course $course, Subscription $subscription, UserSubscription $user_subscription)
    {
        $user = Auth::user();

        if (!$course) {
            return response()->json(['message' => 'Course not found']);
        }

        // Проверяем, существует ли у пользователя активная подписка с переданным ID подписки
        $userSubscription = $user_subscription->where('subscription_id', $subscription->id)
            ->where('user_id', $user->id)
            ->first();

        if ($userSubscription !== $user_subscription->where('subscription_id', $subscription->id)
            ->where('user_id', $user->id)
            ->first()
        ) {
            // Если подписка не существует, создаём её
            $newUserSubscription = new UserSubscription();
            $newUserSubscription->user_id = $user->id;
            $newUserSubscription->subscription_id = $subscription->id;
            $newUserSubscription->save();
        }

        // Теперь мы можем получить цену подписки
        $price = $subscription->getPrice();

        // Получаем сумму на балансе пользователя через свойство объекта баланса
        $userBalance = $user->balance;

        if (!$userBalance) {
            $userBalance = new UserWallet();
            $userBalance->balance = 0;
            $userBalance->user()->associate($user);
            $userBalance->save();
        }

        if ($userBalance->balance < $price) {
            return response()->json(['message' => 'Top up your balance']);
        }

        // Покупаем курс и уменьшаем сумму на балансе пользователя
        $userBalance->balance -= $price;
        $userBalance->save();
        $user->courses()->save($course);

        return response()->json(['success' => 'Course purchased successfully']);
    }



    public function getMyPurchases()
    {
        $user = Auth::user();

        // Получите список курсов, которые пользователь купил, включая информацию о подписке
        $purchasedCourses = $user->courses->map(function ($course) {
            $subscription = $course->subscription;

            // Учитывая, что у каждого курса есть обязательная подписка, можно предположить,
            // что для каждого курса будет ровно одна связанная подписка.

            // Получите информацию о курсе и его подписке
            return [
                'course' => $course,
                // 'subscription_id' => $subscription->id,
                // 'subscription_name' => $subscription->name, // Замените на соответствующие поля подписки
                // 'subscription_price' => $subscription->price,
                // Другие поля подписки, которые вам нужны
            ];
        });

        return response()->json(['purchases' => $purchasedCourses], 200);
    }
}
