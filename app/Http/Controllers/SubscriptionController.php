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
        $subscriptions = $course->subscriptions()->with('descriptions')->get();
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

    /*  public function store(Request $request)
    {
        $data = $request->validate([
            'description' => 'required',
            'name' => 'required|string',
            'subscription_id' => 'array',
            'price' => 'required|integer',
            'duration' => 'required|numeric',
            'duration_type' => 'required|string',
            'course_id' =>  'required|numeric',
        ]);

        $descriptions = [];
        foreach ($data['description'] as $descriptionData) {
            $description = Description::create([
                'description' => $descriptionData['description'],
                'subscription_id' => $descriptionData['subscription_id'],
            ]);
            $descriptions[] = $description;
        }



        $data = [
            'name' => $request->name,
            'price'    => $request->price,
            'duration' => $request->duration,
            'duration_type'  => $request->duration_type,
            'course_id' => $request->course_id,
        ];

        Subscription::create($data);

        return response()->json(['message' => 'Subscription created successfully']);
    }
 */
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
    public function update(Request $request, Subscription $subscription)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'price' => 'required|integer',
            'duration' => 'required|numeric',
            'duration_type' => 'required|string',
            'course_id' =>  'required|integer',
            'description' => 'required',
        ]);
        if (isset($data['description'])) {
            // Преобразование описания в JSON-строку
            $data['name'] = ($data['name']);
            $data['price'] = ($data['price']);
            $data['duration'] = ($data['duration']);
            $data['duration_type'] = ($data['duration_type']);
            $data['course_id'] = ($data['course_id']);
            $data['description'] = json_encode($data['description']);

            // Объединение текущих данных с новыми данными
            $subscription->update(array_merge($subscription->toArray(), $data));
        } else {
            // Если описание не передано, обновить только другие данные
            $subscription->update($data);
        }
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
