<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function getStatisticsUser()
    {
        $users = User::with('courses')->get();

        $courseCount = 0;

        foreach ($users as $user) {
            foreach ($user->courses as $course) {
                $courseCount++;
            }
        }
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
        ]);
    }
}
