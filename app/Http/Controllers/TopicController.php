<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Exception;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    /* function __construct()
    {
        $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    } */
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // All Topics
        $topics = Topic::all();
        // Return Json Response
        return response()->json([
            'topics' => $topics
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
                'id' => $request->id,
                'course_id' => $request->course_id,
                'name' => $request->name,
            ];
            Topic::create($data);
            return response()->json([
                'message' => "Topic succefully created."
            ], 200);
        } catch (\Exception $e) {
            //Return response Json
            return response()->json([], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //Topic detail
        $topic = Topic::find($id);
        if (!$topic) {
            return response()->json([
                'message' => 'Course not found.'
            ], 404);
        }
        // Return Json Response
        return response()->json([
            'topics' => $topic
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
    public function update(Request $request, $id, \Exception $e)
    {
        $data = [
            'id' => $request->id,
            'course_id' => $request->course_id,
            'name' => $request->name,
        ];
        try {
            //find topic
            $topic = Topic::find($id);
            if (!$topic) {
                return response()->json([
                    'message' => $e
                ], 404);
            }
            $data = [
                $topic->name = $request->name,
                $topic->course_id = $request->course_id,
            ];
            $topic->save($data);
            //Return Json Response
            return response()->json([
                'message' => "Topic succefully updated."
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
        $topic = Topic::find($id);
        if (!$topic) {
            return response()->json([
                'message' => 'Topic not found.'
            ], 404);
        }
        $topic->delete();
        return response()->json([
            'message' => "Topic succefully deleted."
        ], 200);
    }
}
