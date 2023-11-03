<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'topic_name'
    ];

    function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }
    public function isFirstLesson(Lesson $lesson)
    {
        return $this->lessons()->orderBy('id')->first()->id === $lesson->id;
    }
}
