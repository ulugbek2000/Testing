<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // All Sessions
        $session = Session::all();
        // Return Json Response
        return response()->json([
            'sessions' => $session
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
                'ip_address' => $request->ip_address,
                'user_agent' => $request->user_agent,
                'payload' => $request->payload,
                'last_activity' => $request->last_activity
            ];
            Session::create($data);
            return response()->json([
                'message' => "Session succefully created."
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
    public function show(string $id)
    {
        //Session detail
        $session = Session::find($id);
        if (!$session) {
            return response()->json([
                'message' => 'Session not found.'
            ], 404);
        }
        // Return Json Response
        return response()->json([
            'courses' => $session
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
            //find course
            $session = Session::find($id);
            if (!$session) {
                return response()->json([
                    'message' => 'Session not found!!'
                ], 404);
            }
            $data = [
                $session->user_id = $request->user_id,
                $session->ip_address = $request->ip_address,
                $session->user_agent = $request->user_agent,
                $session->payload = $request->payload,
                $session->last_activity = $request->last_activity,
            ];
            $session->save($data);
            //Return Json Response
            return response()->json([
                'message' => "session succefully updated."
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
    public function destroy(string $id)
    {
        $session = Session::find($id);
        if (!$session) {
            return response()->json([
                'message' => 'Session not found.'
            ], 404);
        }
        $session->delete();
        return response()->json([
            'message' => "Session succefully deleted."
        ], 200);
    }
}
