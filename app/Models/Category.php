<?php

namespace App\Models;

use App\Traits\OutputDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;


class Category extends Model
{
    use OutputDate, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    protected $appends = ['created_date', 'product_counts'];

    /**
    * Get the tenplate that owns the type.
    */
    public function product() : HasOne
    {
        return $this->hasOne('App\Models\Product');
    }

    public function products() : HasMany
    {
        return $this->hasMany('App\Models\Product');
    }

    public function categorySelections() : HasMany
    {
        return $this->hasMany('App\Models\CategorySelection');
    }

    public function getProducts($merchant_id, $cat_id=null) : Builder
    {
        return $this->whereHas('categorySelections', function($query) use($merchant_id, $cat_id) {
            $query->where("merchant_id", $merchant_id);
            if( $cat_id )  $query->where("category_id", $cat_id);
        });
    }

    /**
     * Get join date
     */
    public function getCreatedDateAttribute() : array
    {
       return $this->encapsulateDate($this->created_at);
    }


    /**
     * Get product counts
     */
    public function getProductCountsAttribute() : int
    {
       return $this->categorySelections()->count();
    }


}
