<?php

namespace App\Models;

use App\Enums\UserType;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasRolesAndPermissions;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Notifications\VerificationNotification;
use Illuminate\Auth\Passwords\CanResetPassword;

class User extends Authenticatable implements JWTSubject
{
    use  HasApiTokens, HasFactory;

    use Notifiable, HasRoles, CanResetPassword;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'phone',
        'city',
        'photo',
        'gender',
        'description',
        'position',
        'date_of_birth',
        'email_verified_at',
        'phone_verified_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'remember_token',
        'is_blocked'
    ];



    public function courses()
    {
        return $this->belongsToMany(Course::class, UserCourse::class);
    }

    public function userSkills()
    {
        return $this->hasMany(UserSkills::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function orderCourses()
    {
        return $this->hasMany(OrderCourse::class);
    }

    public function wallet()
    {
        return $this->hasOne(UserWallet::class);
    }

    public function transaction()
    {
        return $this->hasMany(UserTransaction::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function completedLessons()
    {
        return $this->belongsToMany(Lesson::class, 'user_lessons_progress', 'user_id', 'lesson_id')->withTimestamps();
    }

    public function getJWTCustomClaims()
    {
        return [
            'user_type' => $this->roles()->first()->id, // Получение роли пользователя
            'is_phone_verified' => $this->phone_verified_at != null, 
            'is_email_verified' => $this->email_verified_at != null, 
        ];
    }
    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    function verifyCode($code, $type)
    {
        $notification = $this->unreadNotifications()->where('type', 'App\Notifications\VerificationNotification')->latest()->first();

        $result = ($notification && array_key_exists('verification', $notification->data) && $notification->data['verification'] == $code) ? true : false;

        if ($result) {
            if ($type === 'phone') {
                $this->update(['phone_verified_at' => now()]);
            } elseif ($type === 'email') {
                $this->update(['email_verified_at' => now()]);
            }
        }

        $result && $notification->markAsRead();
        return $result;
    }


    function phoneVerified(): bool
    {
        return $this->phone_verified_at !== null || $this->email_verified_at !== null;
    }

    function isSubscribed(Course $course)
    {
        return  $this->subscriptions()
            ->where('course_id', $course->id)->exists();
    }

    function addProgressCourse(Lesson $lesson)
    {
        UserLessonsProgress::firstOrCreate([
            'user_id' => $this->id,
            'lesson_id' => $lesson->id,
            'course_id' => $lesson->topic->course->id,
            'completed' => true
        ]);

        // Проверяем, существует ли запись пользователя для данного урока в таблице LessonUser
        $existingLessonUser = LessonUser::where('lesson_id', $lesson->id)
            ->where('user_id', $this->id)
            ->first();

        if (!$existingLessonUser) {
            // Если запись не существует, создаем ее и устанавливаем views в 1
            $lesson->increment('views');
            $lesson->save();

            LessonUser::create([
                'lesson_id' => $lesson->id,
                'user_id' => $this->id,
                'views' => 1,
            ]);
        }
    }



    public function sendPasswordResetNotification($token)
    {
        $this->notify(new VerificationNotification($token));
    }
    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }
}
