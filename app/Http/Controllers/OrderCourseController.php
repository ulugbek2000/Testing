<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\OrderCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderCourseController extends Controller
{

    function getApplicationCourse()
    {
        $user = Auth::user();

        if ($user->hasRole(UserType::Admin)) {
            $orders = OrderCourse::paginate(12);
            return response()->json(['Заявки' => $orders], 200);
        }
    }

    public function applicationCourse(Request $request)
    {
        // Валидация входных данных
        $request->validate([
            'name' => 'required|string',
            'surname' => 'required|string',
            'order' => 'required|string',
        ]);

        // Создаем новый заказ
        $orderCourse = new OrderCourse();
        $orderCourse->name = $request->input('name');
        $orderCourse->surname = $request->input('surname');
        $orderCourse->order = $request->input('order');


        // Сохраняем заказ в базе данных
        $orderCourse->save();

        // Возвращаем успешный ответ
        return response()->json(['msg' => 'Курс успешно заказан'], 200);
    }
    function destroy(OrderCourse $orderCourse)
    {
        $orderCourse->delete();
        return response()->json(['msg' => 'Заявка успешно удален']);
    }
}
