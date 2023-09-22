<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Course;
use App\Models\User;
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

    public function purchaseCourse(Request $request)
    {
        $user = Auth::user();
        if (!$user->balance) {
            // Если объект баланса отсутствует, создайте его.
            $balance = new Balance();
            $balance->amount = 0; // Устанавливаем начальный баланс в 0 или другое значение.
            $balance->save();

            // Привяжите объект баланса к пользователю.
            $user->balance()->associate($balance);
            $user->save();
        }

        // Получите идентификатор курса из запроса.
        $courseId = $request->input('course_id');

        // Загрузите объект курса по его идентификатору.
        $course = Course::find($courseId);

        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        // Проверьте, достаточно ли средств на балансе пользователя.
        if ($user->balance->amount < $course->subscription->price) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        // Уменьшите баланс пользователя.
        $user->balance->amount -= $course->subscription->price;
        $user->balance->save();

        // Добавьте пользователя к курсу.
        $user->courses()->attach($course);

        return response()->json(['success' => 'Course purchased successfully'], 200);
    }
}
