<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Events\Transaction;
use App\Models\Course;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\UserTransaction;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function getStatisticsUser()
    {
        // $users = User::with('courses')->get();

        $courseCount = Course::all()->count();
        $subscriptionCount = UserSubscription::latest('created_at')->distinct('user_id')->count('user_id');


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

    // public function getResults($month)
    // {
    //     $students = User::whereHas('roles', function ($query) {
    //         $query->where('name', UserType::Student);
    //     })->count();
    //     $payments = UserTransaction::whereMonth('payment_date', $month)->get();
    //     $subscriptions = Subscription::whereMonth('subscription_date', $month)->get();

    //     return response()->json([]);
    // }


    public function getResults($year)
    {
        $months = [];
    
        for ($month = 1; $month <= 12; $month++) {
            $students = User::whereHas('roles', function ($query) {
                $query->where('name', UserType::Student);
            })->count();
    
            $payments = UserTransaction::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();
    
            $subscriptions = Subscription::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();
    
            $months[] = [
                'name' => date('F', mktime(0, 0, 0, $month, 1, $year)),
                'students' => $students,
                'payments' => $payments,
                'subscriptions' => $subscriptions,
            ];
        }
    
        return response()->json(['months' => $months]);
    }
    
}
