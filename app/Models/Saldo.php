<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Interfaces\Constants;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Saldo extends Model implements Constants
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'receipt_no',
        'amount',
        'merchant_id',
        'type',
        'payment_method',
        'status',
        'payment_data',
        'admin_ok_by',
        'admin_failed_by',
        'paid_at',
        'note'
    ];

    /**
     * Appends var to json
     *
     * @var array
     */
    protected $appends = ['amount_currency', 'usage_currency', 'method_label'];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get amount currency attribute
     *
     * @return void
     */
    public function getAmountCurrencyAttribute()
    {
        return currency($this->amount);
    }

     /**
     * Get usage currency attribute
     *
     * @return void
     */
    public function getUsageCurrencyAttribute()
    {
        return currency($this->usage);
    }

    public function getMethodLabelAttribute()
    {
        if( $this->payment_method === static::PAYMENT_METHOD_CASH ) {
            return __("user.payment_methods.cash");
        } else if( $this->payment_method === static::PAYMENT_METHOD_BANK ) {
            return __("user.payment_methods.bank_transfer");
        } else if( $this->payment_method === static::PAYMENT_METHOD_BONUS ) {
            return __("user.payment_methods.bonus");
        }

    }
}
