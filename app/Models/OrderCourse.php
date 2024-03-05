<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCourse extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'surname',
        'text',
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
