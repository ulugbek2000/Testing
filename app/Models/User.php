<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasRolesAndPermissions;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
// use Illuminate\Foundation\Auth\User as Authenticatable;
// use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    use  HasApiTokens, HasFactory;

    use Notifiable, HasRoles;

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
        return $this->hasManyThrough(Course::class, UserCourse::class);
    }

    public function role()
    {
        return $this->hasMany(Role::class);
    }

    public function userSkills()
    {
        return $this->hasMany(UserSkills::class);
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
        'skills' => 'array', // Определите, что поле 'description' должно быть массивом
    ];

    function verifyCode($code) {
        $notification = $this->unreadNotifications()->where('type', 'App\Notifications\SmsVerification')->latest()->first();
        $result = ($notification && array_key_exists('verification', $notification->data) && $notification->data['verification'] == $code) ? true : false;
        if($result){
            $this->update(['phone_verified_at' => now()]);
        }

        $result && $notification->markAsRead();
        return $result;
    }
}
