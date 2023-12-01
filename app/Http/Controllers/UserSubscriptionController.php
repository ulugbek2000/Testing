<?php

namespace App\Http\Controllers;

use App\Events\Subscription as EventsSubscription;
use App\Models\Course;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSubscriptionController extends Controller
{
    function subscribe(Course $course, Subscription $subscription)
    {
        $user = Auth::user();

        $previousSubscription = $user->subscriptions()->where('course_id', $course->id)->where('subscription_id', $subscription->id)->first();
        dd($previousSubscription);

        if ($previousSubscription) {
            if ($previousSubscription->deleted_at && $previousSubscription->deleted_at < now()) {
                $previousSubscription->delete();
            } else {
                return response()->json(['message' => 'Уже подписан на этот пакет'], 200);
            }
        }

        $userSubscription = $user->subscriptions()->create([
            'course_id' => $course->id,
            'subscription_id' => $subscription->id,
            'price' => $subscription->price,
            'deleted_at' => $subscription->getDurationDateTime()
        ]);

        return response()->json(['message' => $userSubscription]);
    }
}
