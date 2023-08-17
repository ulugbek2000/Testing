<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;
    protected $fillable = [
        'topic_id',
        'name',
        'content',
        'type',
    ];
    // protected   $type = ['video','audio', 'text'];
    function topic() {
        return $this->belongsTo(topic::class, 'topic_id', 'id');
    }
}
