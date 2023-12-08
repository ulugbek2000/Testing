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
                //   $media = $lesson->getMedia('content')->first();
     
             if ($media) {
                 $localPath = $media->getPath();
                 $durationInSeconds = FFProbe::create([
                    'ffmpeg.binaries' => '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffmpeg',
                    'ffprobe.binaries' => '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffprobe',
                 ])->format($localPath)->get('duration');
     
                 $media->setCustomProperty('duration', $durationInSeconds)->save();
                //  $lesson->content = $media->getUrl();
             }
            }
        };
    }
} 