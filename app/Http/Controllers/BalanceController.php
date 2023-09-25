<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Course;
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

    public function purchaseCourse(Request $request, Course $course)
    {
        $user = Auth::user();

        if (!$course) {
            return response()->json(['message' => 'Course not found']);
        }

        if ($user->balance->amount < $course->price) {
            return response()->json(['error' => 'Insufficient balance']);
        }

        // Уменьшите баланс пользователя
        $user->balance->amount -= $course->price;
        $user->balance->save();

        // Добавьте пользователя к курсу
        $userCourse = UserCourse::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course->id
        ], [
            'user_id' => $user->id,
            'course_id' => $course->id
        ]);
        return response()->json(['message' => $userCourse->wasRecentlyCreated ? "User enrolled to course successfuly." : "User already enrolled!"], 200);

        // return response()->json(['success' => 'Course purchased successfully']);
    }
}
