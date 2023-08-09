<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
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
        //All Users
        $users = User::all();
        // Return Json Response
        return response()->json([
            'users' => $users
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
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password,
                'email_verified_at' => $request->email_verified_at,
                'phone_verified_at' => $request->phone_verified_at,
                'two_factor_secret' => $request->two_factor_secret,
                'two_factor_recovery_codes' => $request->two_factor_recovery_codes,
                'two_factor_confirmed_at' => $request->two_factor_confirmed_at,
                'remember_token' => $request->remember_token

            ];
            User::create($data);
            return response()->json([
                'message' => "User succefully created."
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
        //User detail
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }
        // Return Json Response
        return response()->json([
            'user' => $user
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
            //find user
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'User not found!!'
                ], 404);
            }
            $data = [
                $user->name = $request->name,
                $user->email = $request->email,
                $user->phone = $request->phone,
            ];
            $user->save($data);
            //Return Json Response
            return response()->json([
                'message' => "user succefully updated."
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
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }
        $user->delete();
        return response()->json([
            'message' => "User succefully deleted."
        ], 200);
    }
}
