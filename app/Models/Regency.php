<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Regency extends Model
{
    use HasFactory;
   /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
    'name',
    'province_id'
  ];

  public function dropdown()
  {

  }

}
