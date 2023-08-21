<?php

namespace App\Models;

use App\Enums\LessonTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;
    protected $fillable = [
        'topic_id',
        'name',
        'cover',
        'content',
        'duration',
        'type',
    ];
    public function setType($newType)
    {
        if (in_array([LessonTypes::Text])) {
            return new \InvalidArgumentException('Invalid lesson type.');
        }

        $this->attributes['type'] = $newType;
        if ($newType === 'text') {
            $this->setAttribute('type', 'string');
        }
    }

    public function updateLesson($data)
    {
        $this->update($data);
    }

    // protected   $type = ['video', 'audio', 'text'];
    function topic()
    {
        return $this->belongsTo(topic::class, 'topic_id', 'id');
    }
}
