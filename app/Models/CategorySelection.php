<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategorySelection extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'merchant_id',
        'product_id',
        'category_id'
    ];

    public function merchant() : BelongsTo
    {
        return $this->belongsTo('App\Models\Merchant');
    }

    public function product() : BelongsTo
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function category() : BelongsTo
    {
        return $this->belongsTo('App\Models\Category');
    }
}
