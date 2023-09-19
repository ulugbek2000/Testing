<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\SlugOptions;

class Course extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        'logo',
        'name',
        'slug',
        'quantity_lessons',
        'hours_lessons',
        'short_description',
        // 'duration',
        // 'duration_type',
        'video',
        'has_certificate',
        // 'price'
    ];


    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }



    public function users()
    {
        return $this->belongsToMany(User::class, 'course_user', 'course_id', 'user_id');
    }

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    public function hasCertificate()
    {
        return $this->has_certificate !== null;
    }

    public function students()
    {
        return $this->hasManyThrough(User::class, UserCourse::class, 'course_id', 'id');
    }
    public function courseSkills()
    {
        return $this->hasMany(CourseSkills::class);
    }
    public function subscription()
    {
        return $this->hasMany(Subscription::class);
    }
}
