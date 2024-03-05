<?php

namespace App\Http\Controllers;

use App\Models\OrderCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderCourseController extends Controller
{
    public function orderCourse(Request $request)
    {
        // Валидация входных данных
        $request->validate([
            'name' => 'required|string',
            'surname' => 'required|string',
            'order' => 'required|string',
        ]);
    
        // Получаем текущего пользователя
        $user = Auth::user();
    
        // Создаем новый заказ
        $orderCourse = new OrderCourse();
        $orderCourse->name = $request->input('name');
        $orderCourse->surname = $request->input('surname');
        $orderCourse->order = $request->input('order');
    
        // Связываем заказ с текущим пользователем
        $orderCourse->user()->associate($user);
    
        // Сохраняем заказ в базе данных
        $orderCourse->save();
    
        // Возвращаем успешный ответ
        return response()->json(['msg' => 'Курс успешно заказан'], 200);
    }
}
