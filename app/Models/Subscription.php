<?php

namespace App\Models;

use Carbon\Carbon;
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

    function getDurationDateTime()
    {
        $date = Carbon::now();
        if ($this->duration_type == 'year')
            $date->addYears($this->duration);
        if ($this->duration_type == 'month')
            $date->addMonths($this->duration);
        if ($this->duration_type == 'week')
            $date->addWeeks($this->duration);

        return $date;
    }

    public function course()
    {
        return $this->belongsToMany(Course::class, 'course_id', 'id');
    }

    public function description()
    {
        return $this->hasMany(Description::class);
    }
    public function users()
    {
        return $this->hasMany(UserSubscription::class);
    }
    // public function getPrice()
    // {
    //     return $this->attributes['price'];
    // }
}
