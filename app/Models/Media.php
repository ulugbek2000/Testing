<?php

namespace App\Models;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use getID3;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;


class Media extends BaseMedia
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::created(static function (Media $media) {
            if ($media->type === 'video' || $media->type === 'audio') {

                $ffmpeg = FFMpeg::create();

                $duration = $ffmpeg->format($media->getPath())->get('duration');

                $media
                    ->setCustomProperty('duration', $duration)
                    ->save();
            }
        });
    }
}
