<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Traits\OutputDate;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable implements JWTSubject
{
    use OutputDate, Notifiable, HasApiTokens;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
		'name',
		'email',
		'password',
		'phone',
		'referral_no',
		'referred_by',
		'active',
        'last_login',
        'device_no',
        'user_agent',
        'ip_address',
        'app_version',
        'settings'
	];

  	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

     /**
     * The attributes that should be appended to arrays.
     *
     * @var array
     */
    protected $appends = ['last_login_date', 'created_date'];

    /**
     * Route notifications for the Slack channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForSlack($notification)
    {
        return 'https://hooks.slack.com/services/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';
    }

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
    public function getJWTCustomClaims()
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
	 * @return HasOne
	 */
	public function merchant() : HasOne
	{
		return $this->hasOne("App\Models\Merchant");
	}

	/**
	 * Get user's merchant
	 *
	 * @author Dian Afrial
	 */
	public function merchantSelected()
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
    * Get the tenplate that owns the type.
    */
    public function transaction()
    {
        return $this->hasOne('App\Models\Transaction');
    }

    /** Get Latest transaction */
    public function latestTransaciton()
	{
		return $this->hasOne('App\Models\Transaction')->latest();
	}

    /** Get Saldo */
    public function saldo()
    {
        return $this->hasOne('App\Models\Saldo');
    }

    /**
     * Get created date
     */
    public function getCreatedDateAttribute()
    {
       return $this->encapsulateDate($this->created_at);
    }

    /**
     * Get last login
     */
    public function getLastLoginDateAttribute()
    {
        if( $this->last_login ) {
            return $this->encapsulateDate($this->last_login);
        }

        return null;

    }

    public function categorySelections()
    {
        return $this->hasMany('App\Models\CategorySelection');
    }

}
