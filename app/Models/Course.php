<?php

namespace App\Models;

use App\Enums\UserType;
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
        'video',
        'has_certificate',
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

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'user_courses', 'course_id', 'user_id')
            ->whereHas('roles', function ($query) {
                $query->where('id', UserType::Teacher);
            })->with('userSkills');
    }


    public function users()
    {
        return $this->belongsToMany(User::class, 'user_courses', 'course_id', 'user_id');
    }

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    function lessons()
    {
        return $this->hasManyThrough(Lesson::class, Topic::class);
    }

    public function isFirstLesson(Lesson $lesson)
    {
        return $this->lessons()->orderBy('id')->first()->id === $lesson->id;
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
        return $this->belongsTo(Subscription::class);
    }
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    public function userSkills()
    {
        return $this->hasMany(UserSkills::class);
    }
    public function userSubscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'course_id');
    }
}
