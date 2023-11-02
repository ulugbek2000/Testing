<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLessonsProgress extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'lesson_id',
        'course_id',
        'completed'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class , 'lesson_id', 'id');
    }
}
