<?php

namespace App\Models;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use getID3;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\FFProbe as FFMpegFFProbe;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg as SupportFFMpeg;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;


class Media extends BaseMedia
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::created(static function (Media $media) {
            if ($media->type === 'video' || $media->type === 'audio') {

                $ffmpeg = SupportFFMpeg::create([
                    'ffmpeg.binaries' => '/usr/bin/ffmpeg',
                    'ffprobe.binaries' => '/usr/bin/ffprobe',
                ]);

                $duration = $ffmpeg->format($media->getPath())->get('duration');

                $media
                    ->setCustomProperty('duration', $duration)
                    ->save();
            }
        });
    }
}
