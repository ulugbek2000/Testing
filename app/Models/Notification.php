<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Notification extends Model
{
    use HasFactory;
    use Notifiable;
    protected  $fillable = [
        'data',
        'notifiable_type',
        'notifiable_id',

    ];
}
