<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Course;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceController extends Controller
{
    public function deposit(Request $request)
    {
        $user = Auth::user();
        $amount = $request->input('amount');

        if ($amount <= 0) {
            return response()->json(['error' => 'Invalid amount'], 400);
        }

        // Получаем объект баланса пользователя
        $balance = $user->balance;

        // Проверяем, существует ли объект баланса
        if (!$balance) {
            // Если объект баланса отсутствует, создаем новый
            $balance = new Balance();
            $balance->amount = 0; // Устанавливаем начальный баланс
            $balance->user()->associate($user); // Связываем с пользователем
            $balance->save(); // Сохраняем баланс
        }

        // Увеличиваем баланс
        $balance->amount += $amount;

        // Сохраняем изменения
        $balance->save();

        return response()->json(['success' => 'Balance updated successfully'], 200);
    }

    public function purchaseCourse(User $user1, Request $request, Course $course, Subscription $subscription, UserCourse $user_course)
    {
        $user = Auth::user();

        if (!$course) {
            return response()->json(['message' => 'Course not found']);
        }

        if ($subscription) {
            // Теперь мы можем получить цену подписки
            $price = $subscription->getPrice();
        }

        // Получаем цену подписки через метод
        if ($user->balance->amount < $price) {
            return response()->json(['error' => 'Insufficient balance']);
        }
        if ($user && $course) {
            $user->courses()->save($course);
            // Или использовать associate(), если у вас есть соответствующее отношение в модели
            // $user->course()->associate($course);

            return response()->json(['message' => 'User is now enrolled in the course']);
        } else {
            return response()->json(['message' => 'User or course not found'], 404);
        }
        
        // Уменьшите баланс пользователя
        $user->balance->amount -= $price;
        $user->balance->save();

        $user->courses()->associate($course);

        return response()->json(['success' => 'Course purchased successfully']);
    }
}
