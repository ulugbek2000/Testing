<?php

namespace App\Http\Controllers;

use App\Enums\TransactionMethod;
use App\Enums\TransactionStatus;
use App\Models\UserTransaction;
use App\Models\UserWallet;
use Illuminate\Http\Request;

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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserTransaction $transaction)
    {
        try {
            //find transaction
            if (!$transaction) {
                return response()->json([
                    'message' => 'Transaction not found!!'
                ], 404);
            }
            $data = [
                $transaction->wallet_id = $request->wallet_id,
                $transaction->amount = $request->amount,
                $transaction->description = $request->description,
                $transaction->method = $request->method,
                $transaction->status = $request->status
            ];
            $transaction->save($data);
            //Return Json Response
            return response()->json([
                'message' => "transaction succefully updated."
            ], 200);
        } catch (\Exception $e) {
            //Return Json Response
            return response()->json([
                'message' => $e,
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserTransaction $transaction)
    {
        if (!$transaction) {
            return response()->json([
                'message' => 'transaction not found.'
            ], 404);
        }
        $transaction->delete();
        return response()->json([
            'message' => "transaction succefully deleted."
        ], 200);
    }
}
