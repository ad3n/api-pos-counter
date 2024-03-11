<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionSaldo extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'transaction_id',
        'saldo_id',
        'amount'
    ];

    public function transactionItem() : BelongsTo
    {
        return $this->belongsTo('App\Models\TransactionItem');
    }

    public function saldo() : BelongsTo
    {
        return $this->belongsTo('App\Models\Saldo');
    }

}
