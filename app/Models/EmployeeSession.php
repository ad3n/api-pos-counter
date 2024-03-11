<?php

namespace App\Models;

use App\Traits\OutputDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeSession extends Model
{
    use OutputDate, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'employee_id',
        'work_date',
        'begun_at',
        'exited_at',
    ];

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array
     */
    protected $appends = ['display_work_date'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Show human work date
     */
    public function getDisplayWorkDateAttribute()
    {
        return $this->encapsulateDate($this->work_date);
    }
}
