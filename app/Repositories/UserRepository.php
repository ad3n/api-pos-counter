<?php
namespace App\Repositories;

use Auth;
use Hash;
use Cache;
use App\Models\Super;
use App\Models\User;
use App\Models\Merchant;
use App\Models\MerchantType;
use App\Models\Referral;
use App\Models\Country;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Category;
use App\Models\Saldo;
use App\Interfaces\Constants;
use App\Traits\Authentication;
use App\Repositories\User\MerchantTypeTrait;
use Illuminate\Validation\ValidateException;
use Illuminate\Database\QueryException;

class UserRepository implements Constants
{

  use Authentication,
    MerchantTypeTrait;

  /**
   * User Model
   *
   * @author Dian Afrial
   * @return void
   */
  protected $user;

  /**
   * Merchant Model
   *
   * @author Dian Afrial
   * @return void
   */
  protected $merchant;

  /**
   * Cache Driver
   *
   * @author Dian Afrial
   * @return object
   */
  protected $cache;

  /**
   * __constructor
   *
   * @author Dian Afrial
   * @return void
   */
  public function __construct(User $user, Merchant $merchant)
  {
    $this->user = $user;
    $this->merchant = $merchant;
    $this->free_cost = config('global.free_trial.cost');
    $this->free_expiry = config('global.free_trial.expiry_date');
    $this->user_max = config('global.free_trial.user_max');

    $this->cache = Cache::store(config('global.date.driver'));
  }

  /**
   * Get current user
   *
   * @return void
   */
  public function currentUser()
  {
    $data = [
      'user'       => auth()->user(),
      'merchant'  => auth()->user()->merchantSelected()->first(),
      'state'     => [
        'work_date'   => $this->getWorkDate(),
        'open'        => $this->getWorkDate() ? true : false
      ]
    ];

    return $data;
  }

  /**
   * Fetch user merchant
   *
   * @param [type] $id
   * @return void
   */
  public function fetchUserMerchant($id)
  {
    try {
      $user = $this->user->find($id);
      return $user;
    } catch (QueryException $e) {
      abort(400, $e->getMessage());
    }
  }

  /**
   * Get summary merchants
   *
   * @return array
   */
  public function getSummaryMerchants()
  {
    $activeSql = $this->merchant->whereHas('user', function ($query) {
      $query->where('active', 1);
    });

    $noActiveSql = $this->merchant->whereHas('user', function ($query) {
      $query->where('active', 0);
    });

    $activeMerchants = $activeSql->get()->count();

    $newest = $noActiveSql->orderBy("created_at", "desc")->first();

    return [
      'newest' => $newest,
      'actives' => $activeMerchants,
      'total' => [
        'supers' => Super::all()->count(),
        'category' => Category::all()->count(),
        'merchantType' => MerchantType::all()->count()
      ]
    ];
  }

  /**
   * Get merchants paginate with optionally request 'merchant_type', 'month', 'year'
   *
   * @param Request $request
   * @return array
   */
  public function getMerchants($request)
  {
    $defaults = [
      'per_page'      => 20,
      'offset'        => 0,
      'type'          => null,
      'month'         => null,
      'year'          => null,
      'name'          => null,
      'country_id'    => null,
      'province_id'   => null,
      'regency_id'    => null,
      "state"         => null,
    ];

    $args = array_merge($defaults, $request->only([
      'per_page',
      'offset',
      'type',
      'month',
      'year',
      'name',
      'country_id',
      'province_id',
      'regency_id',
      'state'
    ]));

    extract($args);

    $models = $this->merchant;

    if ($state == 'active') {
      $models = $models->whereHas("user", function ($query) {
        $query->where('active', 1);
      });
    } else if ($state == 'not_active') {
      $models = $models->whereHas("user", function ($query) {
        $query->where('active', 0);
      });
    };

    $models = $models->with("merchantType")
      ->with("country")
      ->with("province")
      ->with("regency")
      ->orderBy("created_at", "desc");


    if ($month > 0) {
      $models = $models->whereMonth("created_at", $month);

      if (!$year) {
        $models = $models->whereMonth("created_at", date('Y'));
      }
    }

    if ($year && $year > 0) {
      $models = $models->whereYear("created_at", $year);
    }

    if ($type) {
      $models = $models->where("merchant_type", $type);
    }

    if ($name) {
      $models = $models->where("name", "LIKE", "%{$name}%");
    }

    if ($country_id) {
      $models = $models->where("country_id", $country_id);
    }

    if ($province_id) {
      $models = $models->where("province_id", $province_id);
    }

    if ($regency_id) {
      $models = $models->where("regency_id", $regency_id);
    }

    $models = $models->offset($offset)->take($per_page);

    return $models->get();
  }

  /**
   * Get merchant detail by ID
   *
   * @param int $id
   * @param Request $request
   * @return void
   */
  public function getMerchantDetail($id, $request)
  {
    try {
      $model = $this->merchant->where("id", $id)
        ->with("merchantType")
        ->with("country")
        ->with("province")
        ->with("regency")
        ->first();

      $user = $this->user->find($model->user_id);

      $total = $model->transactionCredits() - $model->transactionDebits();

      $types = (new MerchantType)->get();

      $merchant_types = [];
      if ($types->count() > 0) {
        foreach ($types as $i) {
          $merchant_types[$i->code] = __("user.merchant_type.{$i->code}");
        }
      }

      return array_merge($model->toArray(), [
        'user' => $user,
        'merchant_types' => $merchant_types,
        'transactions' => [
          'count' => $model->transactionCount(),
          'total' => $total,
          'total_currency' => currency($total),
          'credit' => $model->transactionCredits(),
          'credit_currency' => currency($model->transactionCredits()),
          'debit' => $model->transactionDebits(),
          'debit_currency' => currency($model->transactionDebits())
        ]
      ]);
    } catch (QueryException $e) {
      abort(400, $e->getMessage());
    }
  }

  /**
   * Update merchant
   *
   * @param mixed $model
   * @param mixed $request
   * @return void
   */
  public function updateMerchant($model, $request)
  {
    try {
      $res = $model->fill($request->only([
        'name',
        'merchant_type',
        'address'
      ]))->save();

      return [
        'success' => true,
        'messages' => 'Great! the merchant is successfully updated!'
      ];
    } catch (QueryException $e) {
      abort(400, $e->getMessage());
    } catch (HttpException $e) {
      abort($e->getStatusCode(), $e->getMessage());
    }
  }

  /**
   * Update Active User
   *
   * @param mixed $model
   * @param boolean $state
   * @return mixed
   */
  public function updateActiveUser($model, $state)
  {
    try {
      if ($state == 'yes') {
        $model->active = 1;
      } else if ($state == 'no') {
        $model->active = 0;
      }

      $res = $model->save();

      if ($state == 'yes' && $this->checkGetBonuses($model)) {
        $merchant_id = $model->merchant()->first()->id;
        $this->addSaldo($merchant_id);
      }

      return [
        'success' => true,
        'messages' => 'Great! the merchant is successfully updated!'
      ];
    } catch (QueryException $e) {
      abort(400, $e->getMessage());
    } catch (HttpException $e) {
      abort($e->getStatusCode(), $e->getMessage());
    }
  }

  /**
   * Delete user merchant
   *
   * @param int $id
   * @return void
   */
  public function deleteUserMerchant($id)
  {
    $user = $this->user->find($id);

    if (!$user) {
      abort(400, 'Sorry! no user found');
    }

    if ($user->merchant()->first()->transactionCount() > 0) {
      abort(400, 'Sorry! the user unable to permanently deleted due to has transactions');
    }

    $user->forceDelete();

    $res = [
      'success' => true,
      'messages' => 'Success Deleted!',
    ];

    return $res;
  }

  /**
   * is allow or not get bonus
   *
   * @param [type] $model
   * @return boolean
   */
  protected function checkGetBonuses($model)
  {
    return !$model->last_login &&
      is_date_gt($this->free_expiry) ? true : false;
  }

  /**
   * User complete fields
   *
   * @author Dian Afrial
   * @return mixed
   */
  public function userCompleteFields($request)
  {
    try {
      $user = $this->user->find($this->getUser()->id);

      $user->name = $request->input("name");
      $user->email = $request->input("email");

      $user->save();

      $res = [
        'success' => true,
        'messages' => __('user.complete_data')
      ];

      return $res;
    } catch (QueryException $e) {

      abort(400, $e->getMessage());
    }
  }

  /**
   * Merchant complete fields
   *
   * @author Dian Afrial
   * @return mixed
   */
  public function merchantCompleteFields($request)
  {
    try {

      $merchant = $this->merchant->find($this->getUserMerchant()->id);

      if ($request->input("address")) {
        $merchant->address = $request->input("address");
      }

      if ($request->input("country_id")) {
        $merchant->country_id = $request->input("country_id");
      }

      if ($request->input("province_id")) {
        $merchant->province_id = $request->input("province_id");
      }

      if ($request->input("regency_id")) {
        $merchant->regency_id = $request->input("regency_id");
      }

      if ($request->input("open_at")) {
        $merchant->working_open_at = $request->input("open_at");
      }

      if ($request->input("closed_at")) {
        $merchant->working_closed_at = $request->input("closed_at");
      }

      if ($request->input("verified") && $request->input("verified") == 'yes') {
        $merchant->verified = 1;
      } else if ($request->input("verified") && $request->input("verified") == 'no') {
        $merchant->verified = 0;
      }

      if ($request->input('merchant_type')) {
        $merchant->merchant_type = $request->input("merchant_type");
      }

      $merchant->save();

      $res = [
        'success' => true,
        'messages' => __('user.complete_merchant')
      ];

      return $res;
    } catch (QueryException $e) {
      abort(400, $e->getMessage());
    }
  }

  /**
   * Add Saldo for User Free Trial
   *
   * @author Dian Afrial
   * @return void
   */
  public function addSaldo($id)
  {
    $merchant = Merchant::find($id);

    if (!$merchant) {
      abort(400, "Sorry! Merchant not found");
    }

    if (Saldo::where([
      "merchant_id" => $merchant->id
    ])->first()) {
      return false;
    }

    $order_no = generate_receipt_no();
    while (null !== Saldo::where("receipt_no", $order_no)->first()) {
      $order_no = generate_receipt_no();
    }

    $admin_by = auth("admin")->user()->id;

    $data = [
      'amount'           => $this->free_cost,
      'merchant_id'     => $merchant->id,
      'payment_method'   => static::PAYMENT_METHOD_BONUS,
      'receipt_no'       => $order_no,
      'type'             => static::SALDO_TYPE_FREE,
      'admin_ok_by'     => $admin_by,
      'note'            => 'Free Trial Bonus',
      'status'          => 'ok',
      'paid_at'         => current_datetime()
    ];

    Saldo::create($data);

    return true;
  }

  /**
   * Get countries
   *
   * @return void
   */
  public function getCountries()
  {
    $countries = Country::get();
    //dd($countries->toArray());
    $data = [];
    if (!empty($countries)) {
      foreach ($countries as $item) {
        $data[] = [
          'key'       => $item->id,
          'iso_code'  => $item->iso_code,
          'label'     => $item->name,
          'tel_code'  => $item->idd_code
        ];
      }
    }

    return $data;
  }

  /**
   * Get provinces
   *
   * @param string $country_id
   * @return void
   */
  public function getProvinces($country_id = '')
  {
    if ($country_id) {
      $provinces = Province::where("country_id", $country_id)->get();
    } else {
      $provinces = Province::all();
    }

    $data = [];
    if (!empty($provinces)) {
      foreach ($provinces as $item) {
        $data[] = [
          'key'   => $item->id,
          'label' => $item->name
        ];
      }
    }

    return $data;
  }

  public function getRegencies($province_id = '')
  {
    if ($province_id) {
      $regencies = Regency::where("province_id", $province_id)->get();
    } else {
      $regencies = Regency::all();
    }

    $data = [];
    if (!empty($regencies)) {
      foreach ($regencies as $item) {
        $data[] = [
          'key'   => $item->id,
          'label' => $item->name
        ];
      }
    }

    return $data;
  }

  /**
   * Store work date
   *
   * @return array
   */
  public function storeWorkDate($request)
  {
    $merchant = $this->fetchMerchant($this->getUserMerchant()->id);

    $start_time = $merchant->working_open_at;
    $end_time = $merchant->working_closed_at;

    if (!$start_time && !$end_time) {
      abort(400, 'You must set working hours yet');
    }

    if ($request->input("closed") && $request->input("closed") == 'yes') {

      $this->cache->forget($this->getKeyMerchantId());

      return [
        'success' => true,
        'messages' => 'Merchant is closed'
      ];
    }

    // get work date if already exists
    if ($this->getWorkDate()) {
      return [
        'success' => true,
        'date' => $this->getWorkDate()
      ];
    }

    $current_date = current_date();

    // add current date
    $this->addWorkDate($current_date);

    return [
      'success'   => true,
      'date'      => $current_date
    ];
  }

  /**
   * Set activate user by phone
   *
   * @return mixed|boolean
   */
  public function activateUser($username)
  {
    $model = $this->user->where('phone', $username)->first();

    if (!$model) {
      return false;
    }

    if ($model->active == 1) {
      return 'already activated!';
    }

    $model->active = 1;
    $model->save();

    return 'Success activate!';
  }
}
