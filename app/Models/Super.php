<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;
use App\Traits\OutputDate;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Super extends Authenticatable implements JWTSubject
{
    use OutputDate, HasFactory;

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
        'role_id',
        'flag',
        'active',
        'last_login'
    ];

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array
     */
    protected $appends = ['role', 'join_date'];

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
    public function getJWTCustomClaims()
    {
        return [
            'user' => $this->toArray(),
        ];
    }

    /**
     * Get merchant type
     */
    public function getRoleAttribute()
    {
        return $this->role()->title;
    }

    /**
     * Get join date
     */
    public function getJoinDateAttribute()
    {
       return $this->encapsulateDate($this->created_at);
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

	/**
	 * Get user's merchant
	 *
	 * @author Dian Afrial
	 * @return Model
	 */
	public function role()
	{
		return Role::find($this->role_id);
    }
}
