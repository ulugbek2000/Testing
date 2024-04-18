<?php

namespace App\Models;

use App\Enums\LessonTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Lesson extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'topic_id',
        'name',
        'cover',
        'content',
        'type',
        'likes',
        'dislikes',
        'views',
        'order',
        'duration',
        'file_name',
    ];

    function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'id');
    }

    function getContentMedia()
    {
        return $this->getFirstMedia("content");
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function userLessonProgress()
    {
        return $this->hasMany(UserLessonsProgress::class);
    }    

    function getDurationAttribute()
    {
        $media = $this->getMedia('content')->first();

        return $media ? $media->getCustomProperty('duration') ?? 0 : 0;
    }


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('content');
    }

    // public function registerMediaConversions(Media $media = null): void
    // {
    //     $this->addMediaConversion('thumb')
    //         ->width(100)
    //         ->height(100);

    // }
}
