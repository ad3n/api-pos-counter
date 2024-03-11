<?php
namespace App\Repositories;

use Auth;
use Hash;
use App;
use Log;
use App\Models\User;
use App\Models\Super;
use App\Models\Merchant;
use App\Models\MerchantType;
use App\Models\Referral;
use App\Models\Employee;
use App\Models\EmployeeSession;
use App\Interfaces\Constants;
use App\Jobs\Events\NewRegister;
use App\Jobs\Events\NewReferral;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidateException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthRepository implements Constants
{

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
	 * __constructor
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function __construct(User $user, Merchant $merchant)
	{
		$this->user = $user;
		$this->merchant = $merchant;
	}

	/** Maake auth process for user */
	public function userLogin($credentials, $request)
	{
		$credentials['password'] = config('global.defaults.password');

		/*if ( $this->validateNotActive($credentials) ) {
			abort( 401, __('auth.not_active') );
		}*/

		if (
			app()->environment("production")
			&& config("global.device_no.enable_forbidden") === true
		) {
			$list = config("global.device_no.forbidden");

			if (in_array($request->input("device_no"), $list)) {
				abort(401, 'Sorry! You are not allowed accesss this account');
			}
		}

		try {
			$credentials['phone'] = $this->sanitizePhoneNumber($credentials['phone']);
			Log::info("Phone : " . $credentials['phone']);
			// kalau mau pakai guard
			// $token = $this->guard()->attempt($credentials);
			// attempt to verify the credentials and create a token for the user
			if (!$token = auth('api')->attempt($credentials)) {
				abort(401, __('auth.failed_login'));
			}

			// update data login
			$this->updateLogin();
		} catch (ModelNotFoundException $e) {
			// something went wrong whilst attempting to encode the token
			abort(400,  __('auth.failed_token'));
		} catch (JWTException $e) {
			// something went wrong whilst attempting to encode the token
			abort(500,  $e->getMessage());
		}

		$res = [
			"success" 	=> true,
			"token" 	=> $token
		];

		return $res;
	}

	/** Make register for user */
	public function userRegister($request)
	{
		if ($this->checkUserPhone($request->input("phone"))) {
			abort(403, __('register.exists_phone'));
		}

		if (
			$request->input("email") != '' &&
			$this->checkUserEmail($request->input("email"))
		) {
			abort(403, __('register.exists_email'));
		}

		// get referral
		$referral = $this->getReferral($request->input("phone"));

		try {
			// make process register
			$models = $this->processRegister($request, optional($referral)->user_id);

			// make process register
			if ($referral) {
				$this->makeRegisteredReferralStatus(optional($referral)->id);
				event(new NewReferral($referral));
			}
		} catch (QueryException $e) {
			// something went wrong whilst attempting to encode the token
			Log::error("Register Error SQL Query : " . $e->getMessage());

			abort($e->getCode(), $e->getMessage());
		}

		event(new NewRegister($models[0], $models[1]));

		$res = [
			'success' => true,
			'messages' => __('register.success')
		];

		return $res;
	}

	/**
	 * Update user login
	 *
	 * @author Dian Afrial
	 * @return void|ModelNotFoundException
	 */
	protected function updateLogin($guard = 'api')
	{
		$user_id = auth($guard)->user()->id;

		if ($guard == 'api') {
			$res = $this->user->findOrFail($user_id)->fill([
				'last_login' => current_datetime()
			])->save();
		} else if ($guard == 'admin') {
			$res = Super::findOrFail($user_id)->fill([
				'last_login' => current_datetime()
			])->save();
		}
	}

	/** Make auth prcess for admin */
	public function adminLogin($credentials)
	{
		try {

			if ($this->validateSuperFlagged($credentials)) {
				abort(401, __('auth.flagged'));
			}
			// kalau mau pakai guard
			// $token = $this->guard()->attempt($credentials);
			// attempt to verify the credentials and create a token for the user
			if (!$token = auth("admin")->attempt($credentials)) {
				abort(401, __('auth.failed_login'));
			}

			// update data login
			//$this->updateLogin("admin");
		} catch (ModelNotFoundException $e) {
			// something went wrong whilst attempting to encode the token
			abort(400,  __('auth.failed_token'));
		} catch (JWTException $e) {
			// something went wrong whilst attempting to encode the token
			abort(500,  __('auth.failed_token'));
		}

		$res = [
			"success" 	=> true,
			"token" 	=> $token
		];

		return $res;
	}

	/**
	 * Make auth prcess for admin
	 **/
	public function tenantLogin($credentials)
	{
		try {

			if ($this->validateSuperFlagged($credentials, true)) {
				abort(401, __('auth.flagged'));
			}
			// kalau mau pakai guard
			// $token = $this->guard()->attempt($credentials);
			// attempt to verify the credentials and create a token for the user
			if (!$token = auth("employee")->attempt($credentials)) {
				abort(401, __('auth.failed_login'));
			}

			//$payload = auth("employee")->guard("employee")->user()
		} catch (ModelNotFoundException $e) {
			// something went wrong whilst attempting to encode the token
			abort(400,  __('auth.failed_token'));
		} catch (JWTException $e) {
			// something went wrong whilst attempting to encode the token
			abort(500,  __('auth.failed_token'));
		}

		$res = [
			"success" 	=> true,
			"token" 	=> $token
		];

		return $res;
	}

	/** Validate the user is active or not
	 *
	 * @param array $credentials
	 * @return boolean
	 */
	public function validateNotActive($credentials)
	{
		if (empty($credentials))
			return true;

		// find user with active = 0
		$credentials['active'] = 0;

		if ($this->user->where("phone", $this->sanitizePhoneNumber($credentials['phone']))->where("active", 0)->exists()) {
			return true;
		}

		return false;
	}

	/** Validate the user is active or not
	 *
	 * @param array $credentials
	 * @return boolean
	 */
	public function validateSuperFlagged($credentials, $is_tenant = false)
	{
		if (empty($credentials))
			return true;

		if ($is_tenant) {
			// find user with flag = 1
			if (Employee::where('no_hp', $credentials['no_hp'])->where("flag", 1)->exists()) {
				return true;
			}
		} else {
			// find user with flag = 1
			if (Super::where('phone', $credentials['phone'])->where("flag", 1)->exists()) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Check user phone exists or not
	 *
	 * @author Dian Afrial
	 * @return boolean
	 */
	protected function checkUserPhone($phone = '')
	{
		if ($this->user->where("phone", $phone)
			->exists()
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check user email exists or not
	 *
	 * @author Dian Afrial
	 * @return boolean
	 */
	protected function checkUserEmail($email = '')
	{
		if ($this->user->where("email", $email)->exists()) {
			return true;
		}

		return false;
	}

	/**
	 * Check referral and return User ID
	 *
	 * @author Dian Afrial
	 * @return mixed
	 */
	protected function getReferral($phone = '')
	{
		$row = Referral::where("phone", $phone)->where("status", "pending")->first();

		if ($row) {
			return $row;
		}

		return null;
	}

	/**
	 * Update referral status if exists
	 *
	 * @author Dian Afrial
	 * @return boolean
	 */
	protected function makeRegisteredReferralStatus($id)
	{
		$referral = Referral::find($id);

		$referral->status = static::REFERRAL_STATUS_REGISTER;

		$referral->save();

		return true;
	}

	/**
	 * Process register
	 *
	 * @author Dian Afrial
	 * @return mixed|array
	 */
	protected function processRegister($request, $referred_by = null)
	{
		$data = $request->all();

		$this->verifyZeroPhoneNumber($data['phone']);

		$prepare = [
			'name' 		=> $data['name'],
			'phone'		=> $data['phone'],
			'password'  => Hash::make(config('global.defaults.password')),
			'active'	=> 1,
		];

		if ($request->input('email')) {
			$prepare['email'] = $request->input('email');
		}

		if ($referred_by) {
			$prepare['referred_by'] = $referred_by;
		}

		if ($request->input('device_no')) {
			$prepare['device_no'] = $request->input('device_no');
		}

		if ($request->input('user_agent')) {
			$prepare['user_agent'] = $request->input('user_agent');
		}

		if ($request->input('ip_address')) {
			$prepare['ip_address'] = $request->input('ip_address');
		}

		if ($request->input('app_version')) {
			$prepare['app_version'] = $request->input('app_version');
		}

		// insert user
		$model = new $this->user;
		$res = $model->fill($prepare)->save();

		// insert new merchant by user ID
		$merchant = new $this->merchant;
		$merchant->fill([
			'user_id'	=> $model->id,
			'name' 		=> $request->input('merchant_name'),
			'address' 	=> $request->input('merchant_address'),
			'working_open_at' => $request->input('open_at'),
			'working_closed_at' => $request->input('closed_at'),
			'merchant_type'		=> $request->input('merchant_type'),
			'country_id'	=> $request->input('country_id'),
			'province_id'	=> $request->input('province_id'),
			'regency_id'	=> $request->input('regency_id'),
			'number'	=> generate_number(),
		])->save();

		return [
			$model,
			$merchant
		];
	}

	/**
	 * Make sanitize phone number
	 *
	 * @param string $phone
	 * @return string
	 */
	protected function verifyZeroPhoneNumber($phone)
	{
		$phone = strval($phone);

		if (substr($phone, 0, 0) == '0') {
			abort(400, 'Sorry! Format nomor HP tidak valid, awalan 0 telah dipilih sesuai kode negara');
		}

		return true;
	}

	/**
	 * Sanitize phone number
	 *
	 * @param string $phone
	 * @return string
	 */
	protected function sanitizePhoneNumber($phone)
	{
		$phone = strval($phone);

		if ($this->checkUserPhone($phone)) {
			return $phone;
		}

		if (substr($phone, 0, 1) == '0') {
			return substr($phone, -(strlen($phone) - 1));
		} else if (substr($phone, 0, 2) == '62') {
			return substr($phone, -(strlen($phone) - 2));
		}

		return $phone;
	}

	/**
	 * Get merchant types
	 *
	 * @return array
	 */
	public function getMerchantTypes()
	{
		$models = (new MerchantType)->get();

		$data = [];
		if ($models->count() > 0) {
			foreach ($models as $i) {
				$data[$i->code] = $i->name;
			}
		}

		return $data;
	}

	public function logoutEmployee()
	{
		auth("employee")->logout();

		return true;
	}
}
