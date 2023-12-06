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
                $uploadedFile = $media->file;

                if ($uploadedFile) {
                    $media->addMedia($uploadedFile)->toMediaCollection('content');
                    
                    $localPath = $media->getPath();

                    // Используем getID3 для получения информации о медиафайле
                    $getID3 = new getID3();
                    $fileInfo = $getID3->analyze($localPath);

                    if (isset($fileInfo['playtime_seconds'])) {
                        $durationInSeconds = $fileInfo['playtime_seconds'];
                        $media->setCustomProperty('duration', $durationInSeconds)->save();
                    } else {
                        // Обработка случая, когда длительность недоступна
                        logger()->warning('Не удалось определить длительность медиафайла: ' . $localPath);
                        $durationInSeconds = 0; // или другое значение по умолчанию
                    }
                }
            }
        });
    }
}

