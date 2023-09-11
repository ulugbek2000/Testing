<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSkills extends Model
{
    use HasFactory;
    protected $fillable = [
        'skills',
        // 'certificate',
        'user_id'
    ];

    public  function user()
    {
        return $this->belongsTo(User::class);
    }

}
