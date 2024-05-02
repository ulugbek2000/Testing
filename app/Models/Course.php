<?php

namespace App\Models;

use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\SlugOptions;

class Course extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;


    protected $fillable = [
        'logo',
        'name',
        'slug',
        'quantity_lessons',
        'hours_lessons',
        'short_description',
        'video',
        'has_certificate',
        'category_id',
        'is_hidden',
        'language',
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

    public function getPreviousLesson($lesson)
    {
        if ($lesson->topic->course->isFirstLesson($lesson)) {
            return $lesson;
        }
    
        $previousTopic = $this->topics()
            ->whereHas('lessons', function ($query) use ($lesson) {
                $query->where('id', '<', $lesson->id);
            })
            ->with(['lessons' => function ($query) use ($lesson) {
                $query->where('id', '<', $lesson->id)
                    ->orderBy('id', 'desc')
                    ->take(1);
            }])
            ->orderBy('id', 'desc')
            ->first();
    
        if (!$previousTopic) {
            return null; 
        }
    
        $previousLesson = $previousTopic->lessons->first();
   
        if (!$previousLesson) {
            return null;
        }
    
        $userProgress = UserLessonsProgress::where('user_id', auth()->id())
                                            ->where('lesson_id', $previousLesson->id)
                                            ->where('completed', true)
                                            ->exists();
    
        if ($userProgress) {
            return $previousLesson;
        } else {
            return null; 
        }
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
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    // public function categories()
    // {
    //     return $this->belongsToMany(Category::class);
    // }
}
