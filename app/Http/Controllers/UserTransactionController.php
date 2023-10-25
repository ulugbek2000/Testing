<?php

namespace App\Http\Controllers;

use App\Enums\TransactionMethod;
use App\Enums\TransactionStatus;
use App\Models\Course;
use App\Models\Subscription;
use App\Models\UserTransaction;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $transaction = UserTransaction::all();
        // Return Json Response
        return response()->json([
            'transaction' => $transaction
        ], 200);
    }

    public function topUpWallet(Request $request)
    {
        // Валидация данных
        $request->validate([
            'wallet_id' => 'required|exists:user_wallets,id',
            'amount' => 'required|numeric|min:0.01', // Минимальная сумма для пополнения
        ]);

        $wallet = UserWallet::findOrFail($request->input('wallet_id'));

        // Создание записи о транзакции
        $transaction = new UserTransaction([
            'amount' => $request->input('amount'),
            'description' => $request->input('description') ?? 'Пополнить кошелек',
            'method' => TransactionMethod::Cash, // Предполагаем, что вы используете "Cash" как метод пополнения
            'status' => TransactionStatus::Pending, // Предполагаем, что начальный статус "Pending"
        ]);

        $wallet->transactions()->save($transaction);

        // Дополнительная логика для выполнения платежа через карту

        // Здесь вы можете использовать сторонние платежные шлюзы, например, Stripe, Braintree, PayPal, и др.

        // Если платеж успешен, обновите статус транзакции и сумму на счете
        // Пример:
        // $transaction->update([
        //     'status' => TransactionStatus::Success,
        // ]);
        // $wallet->increment('balance', $request->input('amount'));

        // Верните ответ в формате JSON
        return response()->json(['message' => 'Account successfully replenished']);
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

        $user->transactions()->create([ 
            'wallet_id' => $userWallet->id, 
            'amount' => $price,
            'description' => 'Описание транзакции', 
            'method' => TransactionMethod::Cash, 
            'status' => TransactionStatus::Pending, 
        ]);
      

        // Уменьшаем сумму на балансе пользователя
        $userWallet->wallet -= $price;
        $userWallet->save();

        // Создаем запись о подписке
         $user->subscriptions()->create([
            'course_id' => $course->id,
            'subscription_id' => $subscription->id,
            'price' => $subscription->price,
            'deleted_at' => $subscription->getDurationDateTime()
        ]);

        // Предоставляем доступ к курсу
        $user->courses()->attach($course->id);

        return response()->json(['success' => 'Курс успешно куплен']);
    }


    /**
     * Display the specified resource.
     */
    public function show(UserTransaction $transaction)
    {
        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found.'
            ], 404);
        }
        // Return Json Response
        return response()->json([
            'transaction' => $transaction
        ], 200);
    }
}
