<?php

namespace App\Models;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use getID3;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;


class Media extends BaseMedia implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Media $media) {
            if ($media->type === 'video' || $media->type === 'audio') {
                $ffmpeg = FFMpeg::create([
                    'ffmpeg.binaries' => '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffmpeg',
                    'ffprobe.binaries' => '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffprobe'
                ]);

                $uploadedFile = $media->file;

                if ($uploadedFile) {
                    $media->addMedia($uploadedFile)->toMediaCollection('content');
                    $localPath = $media->getPath();

                    $video = $ffmpeg->open($localPath);

                    // Получаем длительность через FFprobe
                    $duration = $video->getDurationInSeconds();
                    // Log::info('Streams: ' . print_r($video->getStreams(), true));
                    // Log::info('Duration: ' . $duration);

                    if ($duration !== null) {
                        $media->setCustomProperty('duration', $duration);
                    }
                }
            }
        });
    }
}
