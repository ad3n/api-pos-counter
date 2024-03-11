<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Role;
use App\Traits\OutputDate;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class Employee extends Authenticatable implements JWTSubject
{
    use OutputDate, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'merchant_id',
        'name',
        'no_hp',
        'password',
        'email',
        'address',
        'role',
        'flag',
        'active_work',
        'begun_at',
        'exited_at',
        'photo'
    ];

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array
     */
    protected $appends = ['join_date'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @return string
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims() : array
    {
        return [
            'user'      => $this->toArray(),
            'merchant'  => $this->merchantSelected()->first()->toArray()
        ];
    }

    /**
     * Get user's merchant
     *
     * @author Dian Afrial
     * @return BelongsTo
     */
    public function merchant() : BelongsTo
    {
        return $this->belongsTo("App\Models\Merchant");
    }

    /**
     * Get user's merchant
     *
     * @author Dian Afrial
     * @return BelongsTo
     */
    public function merchantSelected() : BelongsTo
    {
        return $this->merchant()->select(
            "id",
            "name",
            "number",
            "address",
            "country_id",
            "regency_id",
            "province_id",
            "merchant_type",
            "working_open_at",
            "working_closed_at"
        )->with("merchantType")->with("country")->with("province")->with("regency");
    }

    /**
     * Get join date
     */
    public function getJoinDateAttribute()
    {
        return $this->encapsulateDate($this->created_at);
    }

    public function setPasswordAttribute($value) : void
    {
        $this->attributes['password'] = Hash::make($value);
    }
}
