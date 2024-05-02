<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'wallet_id',
        'amount',
        'description',
        'method',
        'status',
        'total_earnings',
    ];
    function wallet()
    {
        return $this->belongsTo(UserWallet::class, 'wallet_id', 'id');
    }
}
