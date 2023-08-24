<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'price',
        'duration',
        'duration_type',
        'course_id',
        'description'
    ];

    protected $casts = [
        'description' => 'array', // Определите, что поле 'description' должно быть массивом
    ];

    public function course(){
        return $this->belongsTo(Course::class , 'course_id', 'id');
    }
}
