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

    public function purchaseCourse(Course $course, Subscription $subscription)
    {
        $user = Auth::user();
        $previousSubscription = $user->subscriptions()->where('course_id', $course->id)->where('subscription_id', $subscription->id)->first();
    
        if ($previousSubscription) {
            return response()->json(['message' => 'Уже подписан на этот пакет'], 200);
        }
    
        // Получаем сумму на балансе пользователя через свойство объекта баланса
        $userWallet = $user->wallet;
    
        // Получаем цену подписки
        $price = $subscription->price;
    
        // Проверяем, достаточно ли средств на балансе
        if ($userWallet->wallet < $price) {
            return response()->json(['error' => 'Недостаточно средств на балансе'], 400);
        }
    
        // Уменьшаем сумму на балансе пользователя
        $userWallet->wallet -= $price;
        $userWallet->save();
    
        // Создаем запись о подписке
        $userSubscription = $user->subscriptions()->create([
            'course_id' => $course->id,
            'subscription_id' => $subscription->id,
            'price' => $subscription->price,
            'deleted_at' => $subscription->getDurationDateTime()
        ]);
    
        // Предоставляем доступ к курсу
        $user->courses()->attach($course->id);
    
        return response()->json(['success' => 'Курс успешно куплен']);
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
                'subscription_id' => $subscription->id,
                'subscription_name' => $subscription->name, // Замените на соответствующие поля подписки
                'subscription_price' => $subscription->price,
                // Другие поля подписки, которые вам нужны       
            ];
        });

        return response()->json(['purchases' => $purchasedCourses], 200);
    }
}
