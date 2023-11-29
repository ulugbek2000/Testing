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
                // Предполагается, что у вас есть объект $media, представляющий ваш медиа-файл.
            
                $getID3 = new getID3();
            
                // Получаем информацию о файле
                $fileInfo = $getID3->analyze($media->getPath());
            
                // Получаем длительность файла
                $duration = $fileInfo['playtime_seconds'];
            
                // Сохраняем длительность в пользовательских свойствах медиа
                $media->setCustomProperty('duration', $duration)->save();



                // if ($media->type === 'video' or $media->type === 'audio') {

                //       $ffmpeg = FFProbe::create();

                //   	  $duration = $ffmpeg->format($media->getPath())->get('duration');

                //       $media
                //         ->setCustomProperty('duration', $duration)
                //         ->save();

            }
        });
    }
}
