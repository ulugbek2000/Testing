<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'wallet_id',
        'amount',
        'description',
        'method',
        'status'
    ];
    function wallet(){
        return $this->belongsTo(UserWallet::class);
    }
}
