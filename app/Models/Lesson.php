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
        'duration',
        'type',
    ];
    // protected   $type = ['video', 'doc', 'audio', 'text', 'image', 'quiz'];
    public function topic()
    {
        return $this->hasOne(Topic::class);
    }
}
