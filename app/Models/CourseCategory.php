<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCategory extends Model
{
    use HasFactory;
    protected $fillable  = [
        'category_id',
        'course_id'
    ];
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id','id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class , 'course_id', 'id');
    }
}
