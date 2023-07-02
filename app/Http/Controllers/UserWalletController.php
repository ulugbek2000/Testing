<?php

namespace App\Http\Controllers;

use App\Models\UserWallet;
use Illuminate\Http\Request;

class UserWalletController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // All UserWallet
        $wallet = UserWallet::all();
        // Return Json Response
        return response()->json([
            'wallet' => $wallet
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
                'user_id' => $request->user_id,
                'balance' => $request->balance,
            ];
            UserWallet::create($data);
            return response()->json([
                'message' => "Wallet succefully created."
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
        //Wallet detail
        $wallet = UserWallet::find($id);
        if (!$wallet) {
            return response()->json([
                'message' => 'Wallet not found.'
            ], 404);
        }
        // Return Json Response
        return response()->json([
            'wallet' => $wallet
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
    public function update(Request $request, $id)
    {
        try {
            //find course
            $wallet = UserWallet::find($id);
            if (!$wallet) {
                return response()->json([
                    'message' => 'wallet not found!!'
                ], 404);
            }
            $data = [
                $wallet->user_id = $request->user_id,
                $wallet->balance = $request->balance
            ];
            $wallet->save($data);
            //Return Json Response
            return response()->json([
                'message' => "wallet succefully updated."
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
        $wallet = UserWallet::find($id);
        if (!$wallet) {
            return response()->json([
                'message' => 'Wallet not found.'
            ], 404);
        }
        $wallet->delete();
        return response()->json([
            'message' => "Wallet succefully deleted."
        ], 200);
    }
}
