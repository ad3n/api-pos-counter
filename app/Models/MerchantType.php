<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\OutputDate;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MerchantType extends Model
{
    use OutputDate, HasFactory;

    public $incrementing = false;

    /**
     * Primary Key
     *
     * @var string
     */
    protected $primaryKey = 'code';

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name'
    ];

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array
     */
    protected $appends = ['created_date'];

    /**
    * Get merchants by code
    */
    public function merchants() : HasMany
    {
        return $this->hasMany('App\Model\Merchant', 'merchant_type', 'code');
    }

    /**
     * Get join date
     */
    public function getCreatedDateAttribute() : mixed
    {
       return $this->encapsulateDate($this->created_at);
    }

}
