<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SessionController;
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
// Route::get('course', [CourseController::class, 'index']);
// Route::get('course/{id}', [CourseController::class, 'show']);
// Route::post('course', [CourseController::class, 'store']);
// Route::put('courseupdate/{id}', [CourseController::class, 'update']);
// Route::delete('coursedelete/{id}', [CourseController::class, 'destroy']);
Route::post('studentcourse',[CourseController::class,'enrollStudent']);

Route::resource('course', CourseController::class);
//End Courses

//Start Topics
Route::get('topics', [TopicController::class, 'index']);
Route::get('topics/{id}', [TopicController::class, 'show']);
Route::post('topics', [TopicController::class, 'store']);
Route::put('topicupdate/{id}', [TopicController::class, 'update']);
Route::delete('topicdelete/{id}', [TopicController::class, 'destroy']);
//End Topics

//Start Lessons
Route::get('lessons', [LessonController::class, 'index']);
Route::get('lessons/{id}', [LessonController::class, 'show']);
Route::post('lessons', [LessonController::class, 'store']);
Route::put('lessonupdate/{id}', [LessonController::class, 'update']);
Route::delete('lessondelete/{id}', [LessonController::class, 'destroy']);
//End Lessons

//Start Users
Route::get('users', [UsersController::class, 'index']);
Route::get('user/{id}', [UsersController::class, 'show']);
Route::post('users', [UsersController::class, 'store']);
Route::put('usersupdate/{id}', [UsersController::class, 'update']);
Route::delete('usersdelete/{id}', [UsersController::class, 'destroy']);
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

