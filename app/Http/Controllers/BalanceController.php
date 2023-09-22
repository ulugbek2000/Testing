<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceController extends Controller
{
    public function deposit(Request $request)
    {
        $user = Auth::user();
        $amount = $request->input('amount');

        if ($amount <= 0) {
            return response()->json('error', 'Invalid amount');
        }

        $user->balance->amount += $amount;
        $user->balance->save();

        return response()->json('success', 'Balance updated successfully');
    }

    public function purchaseCourse(Request $request, Course $course)
    {
        $user = Auth::user();

        if (!$course) {
            return response()->json('error', 'Course not found');
        }

        if ($user->balance()->amount < $course->subscription->price) {
            return response()->json('error', 'Insufficient balance');
        }

        // Уменьшите баланс пользователя
        $user->balance()->amount -= $course->subscription->price;
        $user->balance->save();

        // Добавьте пользователя к курсу
        $user->courses()->attach($course);

        return response()->json('success', 'Course purchased successfully');
    }
}
