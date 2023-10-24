<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSubscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'course_id',
        'subscription_id'
    ];

    public function course(){
        return $this->belongsTo(Course::class, 'course_id'); 
    }
    public function subscription(){
        return $this->belongsTo(Subscription::class, 'subscription_id'); 
    }

}
