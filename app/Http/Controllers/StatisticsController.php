<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function getStatisticsUser()
    {
        $studentsCount = User::whereHas('roles', function ($query) {
            $query->where('name', UserType::Student);
        })->count();
    
        $teachersCount = User::whereHas('roles', function ($query) {
            $query->where('name', UserType::Teacher);
        })->count();
    
        return response()->json([
            'students_count' => $studentsCount,
            'teachers_count' => $teachersCount,
        ]);
    }
}
