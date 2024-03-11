<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\Merchant;
use Illuminate\Database\QueryException;
use Illuminate\Contracts\Auth\Factory;
use Log;

trait Authentication
{
  /** @var string */
  public $guard = 'api';

  /**
   * Get authenticated user
   *
   * @author Dian Afrial
   * @return mixed
   */
  public function getUser()
  {
    return auth($this->guard)->user();
  }

  /**
   * Get authenticated user
   *
   * @author Dian Afrial
   * @return object
   */
  public function getUserMerchant() : Object
  {
    //dump(auth($guard)->payload()->get("merchant"));
    return auth($this->guard)->user()->get("merchant");
  }

  /**
   * Backwards compability of Date of merchant ID
   *
   * @author Dian Afrial
   * @return string
   */
  public function getKeyMerchantId($id = null)
  {
    return 'date_' . $this->getUserMerchant()->number;
  }

  /**
   * Fetch merchant by ID
   *
   * @param integer $id
   * @return object
   */
  public function fetchMerchant($id)
  {
    try {
      $merchant = Merchant::find($id);
      return $merchant;
    } catch (QueryException $e) {
      abort(400, $e->getMessage());
    }
  }

  /**
   * Get work date
   *
   * @return mixed
   */
  public function getWorkDate()
  {
    if (!$this->cache->has($this->getKeyMerchantId())) {
      return false;
    }

    return $this->cache->get($this->getKeyMerchantId());
  }

  /**
   * Backwards compability of Date
   *
   * @return string
   */
  public function getCurrentDate()
  {
    if ($this->cache->has($this->getKeyMerchantId())) {
      return $this->cache->get($this->getKeyMerchantId());
    }

    return current_date();
  }

  /**
   * Backwards compability ADD method
   *
   * @author Dian Afrial
   * @return boolean
   */
  public function addWorkDate($value)
  {
    $merchant = $this->fetchMerchant($this->getUserMerchant()->id);

    $start_time = $merchant->working_open_at;
    $end_time = $merchant->working_closed_at;

    if ($start_time && $end_time) {
      $startObj = Carbon::parse($start_time);
      $endObj = Carbon::parse($end_time);
      $hours = $endObj->diffInHours($startObj);

      $expiresAt = now()->addHours(intval($hours + config('global.date.aet_close')));
      Log::info("set expiry time of " . $this->getUserMerchant()->id . " : " . $hours . " hours" . " at " . $expiresAt);
    } else {
      $expiresAt = now()->addMinutes(config('global.date.long_expiry'));
      Log::info("set expiry time of " . $this->getUserMerchant()->id . " : " . config('global.date.long_expiry') . " minutes" . " at " . $expiresAt);
    }

    return $this->cache->add($this->getKeyMerchantId(), $value, $expiresAt);
  }
}
