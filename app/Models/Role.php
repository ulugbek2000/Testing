<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'guard_name'
    ];
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'roles_permissions');
    }

    // public function users()
    // {
    //     return $this->belongsTo(User::class);
    // }
}
