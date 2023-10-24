<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSubscription;
use App\Models\Subscription;
use App\Models\UserCourse;
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

    public function purchaseCourse(Course $course, Subscription $subscription, CourseSubscription $course_subscription)
    {
        $user = Auth::user();

        if (!$course) {
            return response()->json(['message' => 'Course not found']);
        }

        if ($subscription) {
            // Теперь мы можем получить цену подписки
            $price = $subscription->getPrice();
        }

        // Получаем сумму на балансе пользователя через свойство объекта баланса
        $userBalance = $user->balance;

        // Проверяем, существует ли объект баланса
        if (!$userBalance) {
            // Если объект баланса отсутствует, создаем новый
            $userBalance = new UserWallet();
            $userBalance->balance = 0; // Устанавливаем начальный баланс
            $userBalance->user()->associate($user); // Связываем с пользователем
            $userBalance->save(); // Сохраняем баланс
        }

        // Уменьшаем сумму на балансе пользователя
        if ($course_subscription) {
            $userBalance->balance -= $price;
            $userBalance->save();

            $user->courses()->save($course);

            return response()->json(['success' => 'Course purchased successfully']);
        } else {
            return response()->json(['message' => 'User or course not found'], 404);
        }
    }

    public function getMyPurchases()
    {
        $user = Auth::user();

        // Получите список курсов, которые пользователь купил, включая информацию о подписке
        $purchasedCourses = $user->courses->map(function ($course) {
            return [
                'course' => $course, // Получение ID курса
                'subscription_id' => $course->subscription->first()->id, // Получение ID подписки
            ];
        });

        return response()->json(['purchases' => $purchasedCourses], 200);
    }
}
