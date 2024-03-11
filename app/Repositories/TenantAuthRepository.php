<?php
namespace App\Repositories;

use Auth;
use Hash;
use App;
use Log;
use App\Models\Employee;
use App\Models\Super;
use App\Models\Merchant;
use App\Models\MerchantType;
use App\Models\Referral;
use App\Interfaces\Constants;
use App\Jobs\Events\NewRegister;
use App\Jobs\Events\NewReferral;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidateException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TenantAuthRepository implements Constants
{

    /**
     * User Model
     *
     * @author Dian Afrial
     * @return void
     */
    protected $employee;

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
    public function __construct(Employee $employee, Merchant $merchant)
    {
        $this->employee = $employee;
        $this->merchant = $merchant;
    }

    /** Make auth prcess for admin */
    public function tenantLogin($credentials)
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
            "success"     => true,
            "token"     => $token
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
    public function validateSuperFlagged($credentials)
    {
        if (empty($credentials))
            return true;

        // find user with flag = 1
        if (Super::where("phone", $credentials['phone'])->where("flag", 1)->exists()) {
            return true;
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
     * @return void
     */
    protected function checkUserEmail($email = '')
    {
        if ($this->user->where("email", $email)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Process register
     *
     * @author Dian Afrial
     * @return void
     */
    protected function processRegister($request, $referred_by = null)
    {
        $data = $request->all();

        $this->verifyZeroPhoneNumber($data['phone']);

        $prepare = [
            'name'         => $data['name'],
            'phone'        => $data['phone'],
            'password'  => Hash::make(config('global.defaults.password')),
            'active'    => 1,
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
            'user_id'    => $model->id,
            'name'         => $request->input('merchant_name'),
            'address'     => $request->input('merchant_address'),
            'working_open_at' => $request->input('open_at'),
            'working_closed_at' => $request->input('closed_at'),
            'merchant_type'        => $request->input('merchant_type'),
            'country_id'    => $request->input('country_id'),
            'province_id'    => $request->input('province_id'),
            'regency_id'    => $request->input('regency_id'),
            'number'    => generate_number(),
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
}
