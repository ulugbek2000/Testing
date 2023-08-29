<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseSkillsController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SessionController;
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

//Start Courses
Route::get('course', [CourseController::class, 'index']);
Route::get('course/{course}', [CourseController::class, 'show']);
Route::post('course', [CourseController::class, 'store']);
Route::put('course/{course}', [CourseController::class, 'update']);
Route::delete('course/{course}', [CourseController::class, 'destroy']);
Route::post('enroll/{course}/{user}', [CourseController::class, 'enroll']);

// Route::resource('course', CourseController::class);
//End Courses

//Start Topics
Route::get('course/{course}/topics', [TopicController::class, 'index']);
Route::get('topic/{topic}', [TopicController::class, 'show']);
Route::post('topic', [TopicController::class, 'store']);
Route::put('topic/{topic}', [TopicController::class, 'update']);
Route::delete('topic/{topic}', [TopicController::class, 'destroy']);
//End Topics

//Start Lessons
Route::get('topic/{topic}/lessons', [LessonController::class, 'index']);
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

//Start Role
Auth::routes();
Route::get('role', [RoleController::class, 'index']);
Route::post('role', [RoleController::class, 'store']);
Route::get('role/{id}', [RoleController::class, 'show']);
Route::put('role/{id}', [RoleController::class, 'update']);
Route::delete('role/{id}', [RoleController::class, 'destroy']);
//End Role

Route::get('/account', function(Request $request){
    return response()->json(auth()->user());
});
