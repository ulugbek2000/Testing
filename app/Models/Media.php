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

                    // Вызываем shell_exec для получения информации о длительности
                    $dur = shell_exec("ffmpeg -i " . $localPath . " 2>&1");

                    if (preg_match("/: Invalid /", $dur)) {
                        // Обработка случая, когда длительность недоступна
                        logger()->warning('Не удалось определить длительность медиафайла: ' . $localPath);
                        $durationInSeconds = 0; // или другое значение по умолчанию
                    } else {
                        // Используем preg_match для извлечения длительности
                        preg_match("/Duration: (.{2}):(..{2}):(..{2})/", $dur, $duration);

                        if (!isset($duration[1])) {
                            $durationInSeconds = 0; // или другое значение по умолчанию
                        } else {
                            // Преобразуем в секунды
                            $hours = $duration[1];
                            $minutes = $duration[2];
                            $seconds = $duration[3];
                            $durationInSeconds = $hours * 3600 + $minutes * 60 + $seconds;
                        }
                    }

                    // Устанавливаем длительность в пользовательские свойства
                    $media->setCustomProperty('duration', $durationInSeconds)->save();
                }
            }
        });
    }
}
