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

        static::saving(static function (Media $media) {
            if ($media->type === 'video' || $media->type === 'audio') {
                $ffmpeg = FFMpeg::create([
                    'ffmpeg.binaries' => '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffmpeg',
                    'ffprobe.binaries' => '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffprobe'
                ]);
        
                $uploadedFile = $media->file;
        
                if ($uploadedFile) {
                    // Добавляем файл к коллекции 'content'
                    $media->addMedia($uploadedFile)->toMediaCollection('content');
        
                    // После добавления файла в коллекцию, вы можете получить путь к нему
                    $localPath = $media->getPath();
        
                    $video = $ffmpeg->open($localPath);
        
                    $duration = $ffmpeg->getFFProbe()->format($video)->get('duration');
        
                    // Проверяем, что длительность больше 0, прежде чем устанавливать ее в качестве пользовательского свойства
                    if ($duration > 0) {
                        $media->setCustomProperty('duration', $duration);
                    }
                }
            }
        });
    }
}
