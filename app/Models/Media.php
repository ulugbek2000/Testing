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

        function (Media $media) {
            if ($media->type === 'video' || $media->type === 'audio') {
                $ffmpegPath = '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffmpeg';
                $ffprobePath =  '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffprobe';


                FFMpeg::setFFMpegBinary($ffmpegPath);
                FFMpeg::setFFProbeBinary($ffprobePath);

                $uploadedFile = $media->file;

                if ($uploadedFile) {
                    $media->addMedia($uploadedFile)->toMediaCollection('content');
                    // $localPath = $media->getPath();

                    // $video = $ffmpegPath->open($localPath);

                    $duration = $video->getDurationInSeconds();
                    if ($duration !== null) {
                        $media->setCustomProperty('duration', $duration);
                    }
                }
            }
        };
    }
}
