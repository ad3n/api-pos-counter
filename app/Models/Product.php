<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * Mass assignement
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'photo',
        'merchant_id',
        'qty',
        'type',
        'regular_price',
        'sale_price',
        'on_sale',
        'capital_cost'
    ];

    protected $appends = ['price', 'category_id', 'quantity'];

    protected $hidden = ['qty'];

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function categorySelection()
    {
        return $this->hasOne('App\Models\CategorySelection');
    }

    /**
     * Get price of product
     *
     * @return integer
     */
    public function price()
    {
        if ($this->on_sale) {
            return $this->sale_price;
        }

        return $this->regular_price;
    }

    public function getPriceAttribute()
    {
        return currency($this->price());
    }

    public function getQuantityAttribute()
    {
        return $this->qty;
    }

    public function getCategoryIdAttribute()
    {
        return optional($this->categorySelection())->category_id;
    }

    /**
     * Get the tenplate that owns the type.
     */
    public function transaction_item() : HasOne
    {
        return $this->hasOne('App\Models\TransactionItem');
    }

    /**
     * Get the tenplate that owns the type.
     */
    public function transactionItems() : HasMany
    {
        return $this->hasMany('App\Models\TransactionItem');
    }
}
