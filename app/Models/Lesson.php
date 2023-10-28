<?php

namespace App\Models;

use App\Enums\LessonTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Enums\;


class Lesson extends Model
{
    use HasFactory;
    protected $fillable = [
        'topic_id',
        'name',
        'cover',
        'content',
        'duration',
        'type'
    ];

    function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'id');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'lesson_user')->withPivot('completed');
    }
}
