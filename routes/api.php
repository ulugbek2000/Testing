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
use App\Http\Controllers\UserLessonProgressController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UserTransactionController;
use App\Http\Controllers\UserWalletController;
use App\Http\Middleware\HasSubscriptionToCourse;
use App\Http\Middleware\RoleMiddleware;
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


Route::get('course', [CourseController::class, 'index']);
Route::get('teacherByCourse/{course}', [CourseController::class, 'getTeacherByCourse']);
Route::get('course/{course}', [CourseController::class, 'show']);

Route::get('courses/search', [CourseController::class, 'search']);

Route::get('course/{course}/subscriptions', [SubscriptionController::class, 'index']);

Route::get('course/{course}/subscriptions', [SubscriptionController::class, 'index']);

Route::get('course/{course}/skill', [CourseSkillsController::class, 'index']);

Route::post('login', [AuthController::class, 'login']);

Route::get('course/{course}/topics', [TopicController::class, 'index']);

Route::get('topic/{topic}/lessons', [LessonController::class, 'index']);
Route::get('lesson/{lesson}', [LessonController::class, 'show']);


Route::middleware(['jwt.auth'])->group(function () {

    Route::middleware(['access:' . UserType::Admin])->group(function () {

        //Пополнение баланс:
        Route::post('admin/balance/deposit', [UserTransactionController::class, 'topUpWallet']);

        //Данный админ
        Route::get('admin/account', [ProfileController::class, 'getProfile']);

        //Update mentor with help Admin
        Route::put('admin/user/{user}',  [ProfileController::class, 'updateTeacher']);

        //get all users
        Route::get('getStudents', [ProfileController::class, 'getAllStudents']);
        Route::get('getTeachers', [ProfileController::class, 'getAllTeachers']);
        Route::get('user/{user}', [ProfileController::class, 'getUserById']);

        //Start Courses
        Route::get('admin/course', [CourseController::class, 'index']);
        Route::get('admin/course/{course}', [CourseController::class, 'show']);
        Route::get('admin/teacherByCourse/{course}', [CourseController::class, 'getTeacherByCourse']);
        Route::post('course', [CourseController::class, 'store']);
        Route::put('course/{course}', [CourseController::class, 'update']);
        Route::delete('course/{course}', [CourseController::class, 'destroy']);
        Route::post('enroll/{course}/{user}', [CourseController::class, 'enroll']);
        Route::get('course/{course}/teacher', [CourseController::class, 'getTeacherInCourse']);
        Route::post('courses/{course}/add-teachers', [CourseController::class, 'addTeachersToCourse']);
        Route::get('courses/{course}/buyers', [CourseController::class, 'getCourseBuyers']);

        //End Courses

        //Start Topics
        Route::get('admin/course/{course}/topics', [TopicController::class, 'index']);
        Route::get('admin/topic/{topic}', [TopicController::class, 'show']);
        Route::post('topic', [TopicController::class, 'store']);
        Route::put('topic/{topic}', [TopicController::class, 'update']);
        Route::delete('topic/{topic}', [TopicController::class, 'destroy']);
        //End Topics

        //Start Lessons
        Route::get('admin/topic/{topic}/lessons', [LessonController::class, 'index']);
        Route::get('admin/lesson/{lesson}', [LessonController::class, 'show']);
        Route::post('lesson', [LessonController::class, 'store']);
        Route::put('lesson/{lesson}', [LessonController::class, 'update']);
        Route::delete('lesson/{lesson}', [LessonController::class, 'destroy']);
        //End Lessons

        //Start CourseSkills
        Route::get('admin/course/{course}/skill', [CourseSkillsController::class, 'index']);
        Route::post('skill', [CourseSkillsController::class, 'store']);
        Route::put('skill/{courseSkills}', [CourseSkillsController::class, 'update']);
        Route::delete('skill/{courseSkills}', [CourseSkillsController::class, 'destroy']);
        //End CourseSkills

        //Start SubscriptionCourse
        Route::get('admin/course/{course}/subscriptions', [SubscriptionController::class, 'index']);
        Route::get('admin/subscription/{subscription}', [SubscriptionController::class, 'show']);
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
        //End Users

        //Start Sessions
        Route::get('sessions', [SessionController::class, 'index']);
        Route::get('session/{id}', [SessionController::class, 'show']);
        Route::post('sessions', [SessionController::class, 'store']);
        Route::put('sessionsupdate/{id}', [SessionController::class, 'update']);
        Route::delete('sessionsdelete/{id}', [SessionController::class, 'destroy']);
        //End Sessions

        //Start Transaction
        Route::get('transactions', [UserTransactionController::class, 'index']);
        Route::post('transactions', [UserTransactionController::class, 'topUpWallet']);
        Route::get('transactions/{id}', [UserTransactionController::class, 'show']);
        //End Transaction

        //Start role
        Route::get('role', [RoleController::class, 'index']);
        Route::post('role', [RoleController::class, 'store']);
        Route::get('role/{id}', [RoleController::class, 'show']);
        Route::put('role/{id}', [RoleController::class, 'update']);
        Route::delete('role/{id}', [RoleController::class, 'destroy']);
        //End Role
    });
    // Route::group(['prefix' => '{lacale}'], function () {
        Route::middleware('access' . implode(',', [UserType::Student]))->group(function () {

            Route::get('student/user/balance', [UserWalletController::class, 'getBalance']);
            Route::get('student/my-purchases', [UserWalletController::class, 'getMyPurchases']);
            Route::get('student/my-purchasesByCourseId/{courseId}', [UserWalletController::class, 'getPurchasesByCourseId']);

            //Обновление своего профиля:
            Route::put('student/profile', [ProfileController::class, 'updateProfile']);

            //Получение списка доступных курсов:
            Route::get('student/course', [CourseController::class, 'index']);

            //Просмотр информации о курсе:
            Route::get('student/teacherByCourse/{course}', [CourseController::class, 'getTeacherByCourse']);
            Route::get('student/course/{course}', [CourseController::class, 'show']);

            //Покупка курса:
            Route::post('student/balance/withdraw/{course}/{subscription}', [UserTransactionController::class, 'purchaseCourse']);

            //Получение списка доступных подписок:
            Route::get('student/course/{course}/subscriptions', [SubscriptionController::class, 'index']);

            Route::get('student/course-progress', [UserLessonProgressController::class, 'getCourseProgress']);

            Route::get('student/topic/{topic}/lessons', [LessonController::class, 'index']);
            Route::get('student/lesson/{lesson}', [LessonController::class, 'show']);

            //Просмотр информации о подписке:
            Route::get('student/subscription/{subscription}', [SubscriptionController::class, 'show']);

            // Получение темы доступных курс:
            Route::get('student/course/{course}/topics', [TopicController::class, 'index']);
            Route::get('student/topic/{topic}', [TopicController::class, 'show']);

            //получение скиллы 
            Route::get('student/course/{course}/skill', [CourseSkillsController::class, 'index']);

            //Верификация на номер:
            Route::post('verify-phone', [AuthController::class, 'verifyPhoneNumber']);

            Route::get('student/account', [ProfileController::class, 'getProfile']);

            Route::post('watched/lesson/{lesson}', [UserLessonProgressController::class, 'watched']);
            Route::get('course/{course}/progress', [UserLessonProgressController::class, 'getprogress']);

            // Route::get('/courses/{course}/buyers', [UserWalletController::class,'getCourseBuyers']);
        });

        Route::post('logout', [AuthController::class, 'logout']);
    });
// });
