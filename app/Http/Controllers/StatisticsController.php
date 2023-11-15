<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\Course;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function getStatisticsUser()
    {
        // $users = User::with('courses')->get();

        $courseCount = Course::all()->count();
        $subscriptionCount = Subscription::all()->count();

        $studentsCount = User::whereHas('roles', function ($query) {
            $query->where('name', UserType::Student);
        })->count();

        $teachersCount = User::whereHas('roles', function ($query) {
            $query->where('name', UserType::Teacher);
        })->count();

        return response()->json([
            'students_count' => $studentsCount,
            'teachers_count' => $teachersCount,
            'course_count' => $courseCount,
            'subscription_count' => $subscriptionCount,
        ]);
    }
}
