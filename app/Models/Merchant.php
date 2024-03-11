<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\OutputDate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Merchant extends Model
{
    use OutputDate, HasFactory;

   /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
		'name',
        'user_id',
        'merchant_type',
		'number',
		'address',
		'type',
		'working_open_at',
		'working_closed_at',
		'verified',
		'country_id',
		'province_id',
		'regency_id'
    ];

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array
     */
    protected $appends = ['join_date', 'saldo', 'location', 'photo'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get user
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

	/**
     * Get country
     */
    public function country() : BelongsTo
    {
        return $this->belongsTo('App\Models\Country');
    }

    /**
     * Get province
     */
    public function province() : BelongsTo
    {
        return $this->belongsTo('App\Models\Province');
    }

    /**
     * Get province
     */
    public function regency() : BelongsTo
    {
        return $this->belongsTo('App\Models\Regency');
    }

    /**
     * Get merchant type
     */
    public function merchantType() : BelongsTo
    {
        return $this->belongsTo('App\Models\MerchantType', 'merchant_type', 'code');
    }

    public function transactions() : HasManyThrough
    {
        return $this->hasManyThrough(
            'App\Models\TransactionItem',
            'App\Models\Transaction',
            'merchant_id', // Foreign key on Transaction table...
            'transaction_id', // Foreign key on TransactionItem table...
            'id', // Local key on Merchant table...
            'id' // Local key on Transaction table...
        );
    }

    public function saldoUsage() : HasManyThrough
    {
        return $this->hasManyThrough(
            'App\Models\TransactionSaldo',
            'App\Models\Saldo',
            'merchant_id', // Foreign key on Saldo table...
            'saldo_id', // Foreign key on TransactionSaldo table...
            'id', // Local key on Merchant table...
            'id' // Local key on Transaction table...
        );
    }

    public function transactionCount() : int
    {
        return $this->transactions()->count();
    }

    public function transactionCredits() : mixed
    {
        return $this->transactions()->sum("credit");
    }

    public function transactionDebits() : mixed
    {
        return $this->transactions()->sum("debit");
    }

    /**
     * Get join date
     */
    public function getJoinDateAttribute() : mixed
    {
       return $this->encapsulateDate($this->created_at);
    }

    public function getLocationAttribute() : string | null
    {
        $country = $province = $regency = null;

        if( $this->country()->first() ) {
            $country = $this->country()->first()->iso_code;
        }

        if( $this->province()->first() ) {
            $province = ucwords(strtolower($this->province()->first()->name));
        }

        if( $this->regency()->first() ) {
            $regency = ucwords(strtolower($this->regency()->first()->name));
        }

        if( $regency && $province && $country ) {
            return $regency . ", " . $province . " - " . $country;
        } else if( $province && $country ) {
            return $province . ", " . $country;
        } else {
            return $country;
        }

        return null;
    }

    /**
     * Get saldo total attribute
     *
     * @return array
     */
    public function getSaldoAttribute() : Array
    {
        $currentUse = $this->hasMany('App\Models\Saldo', 'merchant_id', 'id')
                    ->whereNull('closed_at')
                    ->orderBy("created_at", "asc");

        $saldoChargeCount = $this->saldoUsage()->count();

        $current = $currentUse->first() ? ( $currentUse->first()->amount - $currentUse->first()->usage ) : null;

        return [
            'total'                     => $this->getUsageSaldo(),
            'total_transaction_count'   => $saldoChargeCount,
            'total_currency'            => currency($this->getUsageSaldo()),
            'current'                   => [
                'total'                 => $current,
                'added_at'              => $currentUse->first() ? $this->encapsulateDate( $currentUse->first()->created_at ) : false
            ]
        ];
    }

    public function getUsageSaldo() : float
    {
        $amountUse = $this->hasMany('App\Models\Saldo', 'merchant_id', 'id')
                    ->whereNull("closed_at")
                    ->sum("amount");

        $usageUse = $this->hasMany('App\Models\Saldo', 'merchant_id', 'id')
                    ->whereNull("closed_at")
                    ->sum("usage");

        return doubleval($amountUse - $usageUse);
    }

    /**
     * Get photo url
     *
     * @return null | string
     */
    public function getPhotoAttribute() : null | string
    {
        $path = 'storage/img/constant/';
        if( $this->name ) {
            $letter = substr($this->name, 0, 1);
            $letter = ucfirst($letter);
            return asset("{$path}{$letter}.png");
        }

        return null;
    }


    /**
     * Current saldo
     *
     * @return void
     */
    public function getCurrentSaldoAttribute()
    {
        return null;
    }

}
