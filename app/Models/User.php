<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasRolesAndPermissions;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Notifications\VerificationNotification;
// use Tymon\JWTAuth\Contracts\JWTSubject;

// use Illuminate\Foundation\Auth\User as Authenticatable;
// use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable implements JWTSubject
{
    use  HasApiTokens, HasFactory;

    use Notifiable, HasRoles;

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
        'user_type',
        'gender',
        'description',
        'position',
        'date_of_birth',
        'email_verified_at',
        'phone_verified_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'remember_token'
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
    public function purchases()
    {
        return $this->hasMany(UserSubscription::class);
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

    public function getJWTCustomClaims()
    {
        return [
            'user_type' => $this->roles()->first()->id, // Получение роли пользователя
            'is_phone_verified' => $this->phone_verified_at != null, // Проверка статуса верификации телефона
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

    function verifyCode($code)
    {
        $notification = $this->unreadNotifications()->where('type', 'App\Notifications\VerificationNotification')->latest()->first();

        $result = ($notification && array_key_exists('verification', $notification->data) && $notification->data['verification'] == $code) ? true : false;

        if ($result) {
            $this->update(['phone_verified_at' => now()]);
        }

        $result && $notification->markAsRead();
        return $result;
    }

    function phoneVerified(): bool
    {
        return $this->phone_verified_at != null;
    }

    public function hasSubscriptionToCourse(Course $course)
    {
        return $this->subscriptions()
            ->where('course_id', $course->id)
            ->where('status', 'active') // Подписка активна
            ->count() > 0;
    }
}
