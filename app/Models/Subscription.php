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
    ];

    public function course()
    {
        return $this->belongsToMany(Course::class, 'course_id', 'id');
    }
    public function description()
    {
        return $this->hasMany(Description::class);
    }
    public function courseSubscription()
    {
        return $this->hasMany(CourseSubscription::class);
    }
    public function getPrice()
    {
        return $this->attributes['price'];
    }
}
