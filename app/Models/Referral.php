<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Referral extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'saldo_id',
        'user_id',
        'merchant_id',
        'phone',
        'status',
        'amount',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
