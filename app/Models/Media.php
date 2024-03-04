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
     
        });
    }
}
