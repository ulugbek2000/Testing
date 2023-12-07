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
        'type'
    ];

    function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'id');
    }

    function getContentMedia() {
        return $this->getFirstMedia("content");
    }

    function getDurationAttribute() {
        return $this->getContentMedia()->getCustomProperty('duration') ?? 0;
    }
    
    // public function registerMediaConversions(Media $media = null): void
    // {
    //     $this->addMediaConversion('thumb')
    //         ->width(100)
    //         ->height(100);
        
    // }
}
