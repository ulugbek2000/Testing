<?php

namespace App\Http\Controllers;

use App\Models\UserTransaction;
use Illuminate\Http\Request;

class UserTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transaction = UserTransaction::all();
        // Return Json Response
        return response()->json([
            'transaction' => $transaction
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = [
                'wallet_id' => $request->wallet_id,
                'amount' => $request->amount,
                'description' => $request->description,
                'method' => $request->method,
                'status' => $request->status
            ];
            UserTransaction::create($data);
            return response()->json([
                'message' => "Transaction succefully created."
            ], 200);
        } catch (\Exception $e) {
            //Return response Json
            return response()->json([
                'message' => $e,
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $transaction = UserTransaction::find($id);
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
    public function update(Request $request, string $id)
    {
        try {
            //find transaction
            $transaction = UserTransaction::find($id);
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
    public function destroy($id)
    {
        $transaction = UserTransaction::find($id);
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
