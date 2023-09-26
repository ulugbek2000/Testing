<?php

use App\Enums\UserType;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseSkillsController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UserTransactionController;
use App\Http\Controllers\UserWalletController;
use App\Models\UserTransaction;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware(['admin.api'])->group(function () {
});

// Route::middleware('auth.custom')->group(function () {


//Auth Routes
Auth::routes([
    'register' => true,
    'login' => false,
    'logout' => false
]);

Route::post('balance/deposit', [UserWalletController::class, 'deposit']);
Route::post('balance/withdraw/{course}/{subscription}', [UserWalletController::class, 'purchaseCourse']);


Route::post('login', [AuthController::class, 'login']);

Route::get('course', [CourseController::class, 'index']);

Route::get('course/{course}/topics', [TopicController::class, 'index']);

Route::get('topic/{topic}/lessons', [LessonController::class, 'index']);

// });

Route::middleware(['auth:sanctum'])->group(function () {

    Route::middleware(['access:' . UserType::Admin])->group(function () {

        //Update mentor with help Admin
        Route::put('/user/{user}',  [ProfileController::class, 'updateTeacher']);

        //get all users
        Route::get('getStudents', [ProfileController::class, 'getAllStudents']);
        Route::get('getTeachers', [ProfileController::class, 'getAllTeachers']);
        Route::get('user/{user}', [ProfileController::class, 'getUserById']);
        //Start Courses

        Route::get('course/{course}', [CourseController::class, 'show']);
        Route::post('course', [CourseController::class, 'store']);
        Route::put('course/{course}', [CourseController::class, 'update']);
        Route::delete('course/{course}', [CourseController::class, 'destroy']);
        Route::post('enroll/{course}/{user}', [CourseController::class, 'enroll']);
        Route::post('/courses/{course}/add-teachers', [CourseController::class, 'addTeachersToCourse']);
        //End Courses

        //Start Topics
        Route::get('topic/{topic}', [TopicController::class, 'show']);
        Route::post('topic', [TopicController::class, 'store']);
        Route::put('topic/{topic}', [TopicController::class, 'update']);
        Route::delete('topic/{topic}', [TopicController::class, 'destroy']);
        //End Topics

        //Start Lessons
        Route::get('lesson/{lesson}', [LessonController::class, 'show']);
        Route::post('lesson', [LessonController::class, 'store']);
        Route::put('lesson/{lesson}', [LessonController::class, 'update']);
        Route::delete('lesson/{lesson}', [LessonController::class, 'destroy']);
        //End Lessons

        //Start CourseSkills
        Route::get('course/{course}/skill', [CourseSkillsController::class, 'index']);
        Route::post('skill', [CourseSkillsController::class, 'store']);
        Route::put('skill/{courseSkills}', [CourseSkillsController::class, 'update']);
        Route::delete('skill/{courseSkills}', [CourseSkillsController::class, 'destroy']);
        //End CourseSkills

        //Start SubscriptionCourse
        Route::get('course/{course}/subscriptions', [SubscriptionController::class, 'index']);
        Route::post('subscription', [SubscriptionController::class, 'store']);
        Route::put('subscription/{subscription_id}', [SubscriptionController::class, 'update']);
        Route::delete('subscription/{subscription}', [SubscriptionController::class, 'destroy']);
        //End SubscriptionCourse

        //Start Users
        Route::get('users', [UsersController::class, 'index']);
        Route::get('user/{users}', [UsersController::class, 'show']);
        Route::post('users', [UsersController::class, 'store']);
        Route::put('users/{users}', [UsersController::class, 'update']);
        Route::delete('users/{users}', [UsersController::class, 'destroy']);
        Route::get('/users/{users}/role', [UsersController::class, 'showUserRole']);

        //End Users

        //Start Sessons
        Route::get('sessions', [SessionController::class, 'index']);
        Route::get('session/{id}', [SessionController::class, 'show']);
        Route::post('sessions', [SessionController::class, 'store']);
        Route::put('sessionsupdate/{id}', [SessionController::class, 'update']);
        Route::delete('sessionsdelete/{id}', [SessionController::class, 'destroy']);
        //End Sessions

        //Start User Wallets
        Route::get('wallets', [UserWalletController::class, 'index']);
        Route::post('wallets', [UserWalletController::class, 'store']);
        Route::get('wallets/{id}', [UserWalletController::class, 'show']);
        Route::put('walletupdate/{id}', [UserWalletController::class, 'update']);
        Route::delete('walletdelete/{id}', [UserWalletController::class, 'destroy']);
        //End User Wallets

        //Start Transaction
        Route::get('transactions', [UserTransactionController::class, 'index']);
        Route::post('transactions', [UserTransactionController::class, 'store']);
        Route::get('transactions/{id}', [UserTransactionController::class, 'show']);
        Route::put('transactionupdate/{id}', [UserTransactionController::class, 'update']);
        Route::delete('transactiondelete/{id}', [UserTransactionController::class, 'destroy']);
        //End Transaction


        Route::get('role', [RoleController::class, 'index']);
        Route::post('role', [RoleController::class, 'store']);
        Route::get('role/{id}', [RoleController::class, 'show']);
        Route::put('role/{id}', [RoleController::class, 'update']);
        Route::delete('role/{id}', [RoleController::class, 'destroy']);
        //End Role
    });

    Route::middleware('access:' . implode(',', [UserType::Student]))->group(function () {
        
        Route::put('profile', [ProfileController::class, 'updateProfile']);
        Route::post('verify-phone', [AuthController::class, 'verifyPhoneNumber']);
    });

    Route::post('logout', [AuthController::class, 'logout']);
    


    Route::get('/account', function () {
        return response()->json(Auth::check() ? [auth()->user(), 200] : [null, 401]);
    });
});
