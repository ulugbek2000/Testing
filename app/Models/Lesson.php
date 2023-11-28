<?php

namespace App\Models;

use App\Enums\LessonTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class Lesson extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
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

    function getContent() {
        return $this->getMedia("content");
    }
}
