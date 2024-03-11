<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Interfaces\Constants;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentCreditLog extends Model implements Constants
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_no',
        'customer_id',
        'merchant_id',
        'employee_id',
        'type',
        'total',
        'payment_status',
        'payment_method',
        'purchased_at',
        'paid_at',
        'note'
    ];

    /**
     * Appends var to json
     *
     * @var array
     */
    protected $appends = ['merchant', 'customer'];

    /**
     * Get amount currency attribute
     *
     * @return void
     */
    public function getMerchantAttribute()
    {
        return Merchant::find($this->merchant_id);
    }

    /**
     * Get customer object
     */
    public function getCustomerAttribute() : mixed
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id')->first();
    }
}
