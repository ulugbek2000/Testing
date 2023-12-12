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

    protected $fillable = [
        'model_id',
        'custom_properties'
    ];
    protected static function boot()
    {
        parent::boot();

        static::saved(function (Media $media) {
        //     if ($media->type === 'video' || $media->type === 'audio') {
        //         $uploadedFile = $media->file;

        //         if ($uploadedFile) {
        //             $ffmpeg = FFProbe::create([
        //                 'ffmpeg.binaries' => '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffmpeg',
        //                 'ffprobe.binaries' => '/home/softclub/domains/lmsapi.softclub.tj/ffmpeg-git-20231128-amd64-static/ffprobe'
        //             ]);

        //             $localPath = $media->getPath();

        //             $video = $ffmpeg->open($localPath);

        //             $duration = $ffmpeg->format($video)->get('duration');

        //             $media->setCustomProperty('duration', $duration)->save();
        //         }
        //     }
        });
    }
}
