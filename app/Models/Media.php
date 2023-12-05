<?php

namespace App\Models;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use getID3;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;


class Media extends BaseMedia implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected static function boot()
    {
        parent::boot();

        static::created(static function (Media $media) {
            if ($media->type === 'video' || $media->type === 'audio') {
                $ffmpeg = FFMpeg::create([
                    'ffmpeg.binaries' => '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffmpeg',
                    'ffprobe.binaries' => '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffprobe'
                ]);

                // Получаем объект файла из запроса
                $uploadedFile = $media->file;

                // Сохраняем файл локально
                $localPath = $uploadedFile->store('content');

                $video = $ffmpeg->open($localPath);

                $duration = $ffmpeg->getFFProbe()->format($video)->get('duration');

                $media
                    ->setCustomProperty('duration', $duration)
                    ->save();
            }
        });
    }
}

