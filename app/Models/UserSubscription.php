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
        static::creating(function () {
            if (!Course::find($this->course_id)) {
                return response()->json(['Курс не существует']);
            }

            if (!User::find($this->user_id)) {
                return response()->json(['Пользователь не существует']);
            }

            if (!Subscription::find($this->user_id)) {
                return response()->json(['Подписка не существует']);
            }

            if ($this->price < 0) {
                return response()->json(['Цена должна быть больше или равна 0.']);
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
