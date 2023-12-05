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
                $ffmpeg = FFMpeg::create([
                    'ffmpeg.binaries' => '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffmpeg',
                    'ffprobe.binaries' => '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffprobe'
                ]);

                $video = $ffmpeg->open($media->getUrl());

                $duration = $ffmpeg->getFFProbe()->format($video)->get('duration');


                $media
                    ->setCustomProperty('duration', $duration)
                    ->save();
            }
        });
    }
}
