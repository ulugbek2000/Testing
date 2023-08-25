<?php

namespace App\Http\Controllers;

use App\Enums\DurationType;
use App\Enums\SubscriptionType;
use App\Models\Course;
use App\Models\Description;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Ramsey\Uuid\Uuid;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Course $course)
    {
        $subscriptions = $course->subscription()->with('description')->get();
        return response()->json($subscriptions);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request, Subscription $subscription)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'duration' => 'required|numeric',
            'duration_type' => 'required|string',
            'course_id' => 'required|numeric',
            'description' => 'array', // Массив описаний
        ]);

        $subscription = Subscription::create([
            'name' => $data['name'],
            'price' => $data['price'],
            'duration' => $data['duration'],
            'duration_type' => $data['duration_type'],
            'course_id' => $data['course_id'],
        ]);

        // $subscription = Subscription::findOrFail($subscription);

        if (isset($data['description'])) {
            foreach ($data['description'] as $descriptionData) {
                Description::create([
                    'subscription_id' => $subscription->id,
                    'description' => $descriptionData['description'],
                ]);
            }
        }

        return response()->json(['message' => 'Subscription created successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscription $subscription)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $subscriptionId)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'price' => 'required|integer',
            'duration' => 'required|numeric',
            'duration_type' => 'required|string',
            'course_id' =>  'required|integer',
            'description' => 'required',
        ]);
        $subscription = Subscription::findOrFail($subscriptionId);
        $subscription->name = $request->input('name');
        $subscription->price = $request->input('price');
        $subscription->duration = $request->input('duration');
        $subscription->duration_type = $request->input('duration_type');
        $subscription->course_id = $request->input('course_id');
        // Обновите другие поля, если необходимо

        // Обновление описания, если есть
        if ($request->has('description')) {
            $descriptionData = $request->input('description');
            $description = Description::findOrFail($descriptionData['id']); 
            $description->description = $descriptionData['descriptions'];
            $description->save();
        }
        $subscription->save();

        return response()->json(['message' => 'Subscription updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {
        $subscription->delete();
        return response()->json(['message' => 'Subscription soccessfulle daletede'], 200);
    }
}
