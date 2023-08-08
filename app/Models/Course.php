<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        'logo',
        'name',
        'short_description',
        'quantity_lessons',
        'hours_lessons',
        'description',
        'video',
        'has_certificate',
        'price'
    ];
    public function users()
    {
        return $this->belongsToMany(Users::class);
    }
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }
    public function hasCertificate()
    {
        return $this->has_certificate !== null;
    }
}
