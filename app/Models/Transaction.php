<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_no',
        'order_name',
        'work_date',
        'merchant_id',
        'status',
        'type',
        'payment_method',
        'payment_status',
        'paid_at',
        'employee_id',
        'customer_id'
    ];

    protected $appends = ['items', 'name', 'customer'];

    public function user() : BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Get Many Eloquent Model
     *
     * @return HasMany
     */
    public function transactionItem()
    {
        return $this->hasMany('App\Models\TransactionItem');
    }

    /**
     * Get collection transaction items
     *
     * @return Collection
     */
    public function getItems()
    {
        return $this->transactionItem()->get();
    }

    public function transactionSaldo() : HasOne
    {
        return $this->hasOne('App\Model\TransactionSaldo', 'transaction_id', 'id');
    }

    public function lastDateRecords($date = '')
    {
        if (empty($date)) {
            return $this->where("work_date", date('Y-m-d'));
        } else {
            return $this->where("work_date", $date);
        }
    }

    public function getDateRecords($date = '')
    {
        if (empty($date)) {
            return $this->where("work_date", date('Y-m-d'));
        } else {
            return $this->where("work_date", $date);
        }
    }

    public function getCustomerAttribute()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id')->first();
    }

    public function getItemsAttribute()
    {
        return $this->getItems();
    }

    public function getNameAttribute()
    {
        if ($this->getItems()->count() == 1) {
            return $this->getItems()->first()->name;
        } else {
            return 'Lebih dari ' . $this->getItems()->count() . ' items';
        }
    }
}
