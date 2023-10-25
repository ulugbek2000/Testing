<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'subscription_id',
        'user_id',
        'price',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!Course::find($model->course_id)) {
                throw new \Exception('Курс не существует');
            }
        
            if (!User::find($model->user_id)) {
                throw new \Exception('Пользователь не существует');
            }
        
            if (!Subscription::find($model->subscription_id)) {
                throw new \Exception('Подписка не существует');
            }
        
            if ($model->price < 0) {
                throw new \Exception('Цена должна быть больше или равна 0.');
            }
        });
        
    }

    function course()
    {
        return $this->belongsTo(Course::class);
    }

    function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    function user()
    {
        return $this->belongsTo(User::class);
    }
}
