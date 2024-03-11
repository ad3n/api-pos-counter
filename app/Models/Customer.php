<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\OutputDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Interfaces\Constants;

class Customer extends Model implements Constants
{
    use OutputDate, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'no_hp',
        'no_hp_2',
        'no_hp_3',
        'pln_token',
        'bpjs',
        'gopay_va',
        'maxim_id',
        'dana_va',
        'ovo_va',
        'shopee_va',
        'email',
        'address',
        'note',
        'created_by'
    ];

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array
     */
    protected $appends = ['join_date', 'credits_count', 'trx_count', 'credits_total'];

    /**
     * Get join date
     */
    public function getJoinDateAttribute() : array
    {
        return $this->encapsulateDate($this->created_at);
    }

    public function transactions() : HasMany
    {
        return $this->hasMany('App\Models\Transaction', 'customer_id', 'id');
    }

    public function getTrxCountAttribute() : int
    {
        return $this->transactions()->get()->count();
    }

    public function getCreditTransactions() : Collection
    {
        return $this->hasMany('App\Models\Transaction', 'customer_id', 'id')
            ->where('payment_status', static::PAYMENT_STATUS_CREDIT)
            ->get();
    }

    public function getCreditsTotalAttribute() : mixed
    {
        return $this->getCreditTransactions()->sum(function ($collection) {
            return $collection->getItems()->sum('total');
        });
    }

    public function getCreditsCountAttribute() : int
    {
        return $this->getCreditTransactions()->count();
    }
}
