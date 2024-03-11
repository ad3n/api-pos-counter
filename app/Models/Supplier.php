<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Interfaces\Constants;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model implements Constants
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'address',
        'merchant_id',
        'phone',
        'telp',
        'sales_person',
        'sales_contact',
        'country_id',
        'province_id',
        'regency_id'
    ];

    /**
     * Appends var to json
     *
     * @var array
     */
    protected $appends = ['merchant'];


    /**
     * Get amount currency attribute
     *
     * @return void
     */
    public function getMerchantAttribute()
    {
        return Merchant::find($this->merchant_id);
    }
}
