<?php

namespace App\Http\Controllers;

use App\Models\Course;
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
        $balances = $request->input('balance');

        if ($balances <= 0) {
            return response()->json(['error' => 'Invalid amount'], 400);
        }

        // Получаем объект баланса пользователя
        $balances = $user->balance;

        // Проверяем, существует ли объект баланса
        if (!$balances) {
            // Если объект баланса отсутствует, создаем новый
            $balance = new UserWallet();
            $balance->balance = 0; // Устанавливаем начальный баланс
            $balance->user()->associate($user); // Связываем с пользователем
            $balance->save(); // Сохраняем баланс
        }

        // Увеличиваем баланс
        $balances->balance += $balance;

        // Сохраняем изменения
        $balances->save();

        return response()->json(['success' => 'Balance updated successfully'], 200);
    }

    public function purchaseCourse(Course $course,Subscription $subscription, UserCourse $user_course)
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
        if ($user->balance->balance < $price) {
            return response()->json(['error' => 'Insufficient balance']);
        }

        // Уменьшите баланс пользователя
        if ($user_course) {
            $user->balance->balance -= $price;
            $user->balance->save();

            $user->courses()->save($course);

            return response()->json(['success' => 'Course purchased successfully']);
        } else {
            return response()->json(['message' => 'User or course not found'], 404);
        }
    }
}
