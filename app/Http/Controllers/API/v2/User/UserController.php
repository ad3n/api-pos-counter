<?php

namespace App\Http\Controllers\API\v2\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use App\Repositories\UserRepository;
use Illuminate\Validation\Rule;
use App\Interfaces\Constants;
use Auth;
use Validator;

class UserController extends Controller implements Constants
{   
    /**
     * Engine Auth Repository
     *
     * @author Dian Afrial
     * @return object
     */
	protected $engine;

	/** Saldo Repository */
	protected $saldo;

	/**
	 * Get User Info
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function userGet()
	{
		$data = $this->engine->currentUser();

		return response()->json($data, 200);
	}

    /**
     * __constuctor
     *
     * @author Dian Afrial
     * @return void
     */
    public function __construct(
		UserRepository $engine, 
		\App\Repositories\SaldoRepository $saldo 
	)
    {
		$this->saldo = $saldo;
        $this->engine = $engine;
    }

	/**
	 * Handle Update User
	 *
	 * @author Dian Afrial
	 * @return void
	 */
    public function updateUser(Request $request)
    {
		try {
			// validate first
			$this->userValidation($request);

			// make process login
			$res = $this->engine->userCompleteFields( $request );

			// if success throw 200 OK
			return response()->json($res, 200);

		} catch(ValidationException $e) {
			return response()->json( error_json($e->errors()), $e->status );
		} catch(HttpException $e) {
			return response()->json( error_json($e->getMessage()), $e->getStatusCode());
		}
	}
	
	/**
	 * Handle Update Merchant
	 *
	 * @author Dian Afrial
	 * @return void
	 */
    public function updateMerchant(Request $request)
    {
		try {
			// validate first
			$this->merchantValidation($request);

			// make process login
			$res = $this->engine->merchantCompleteFields( $request );

			// if success throw 200 OK
			return response()->json($res, 200);

		} catch(ValidationException $e) {
			return response()->json( error_json($e->errors()), $e->status );
		} catch(HttpException $e) {
			return response()->json( error_json($e->getMessage()), $e->getStatusCode());
		}
	}

	/**
	 * Get saldo by merchant
	 *
	 * @author Dian Afrial
	 * @return void
	 */
    public function getSaldo(Request $request)
    {
		try {
			// make process login
			$res = $this->saldo->getSaldoMerchant();

			// if success throw 200 OK
			return response()->json($res, 200);

		} catch(ValidationException $e) {
			return response()->json( error_json($e->errors()), $e->status );
		} catch(HttpException $e) {
			return response()->json( error_json($e->getMessage()), $e->getStatusCode());
		}
	}

	/**
	 * Store current word date
	 *
	 * @param Request $request
	 * @return void
	 */
	public function putWorkDate(Request $request)
	{
		try {
			// make process login
			$res = $this->engine->storeWorkDate( $request );

			// if success throw 200 OK
			return response()->json($res, 200);

		} catch(ValidationException $e) {
			return response()->json( error_json($e->errors()), $e->status );
		} catch(HttpException $e) {
			return response()->json( error_json($e->getMessage()), $e->getStatusCode());
		}
	}
	
	/**
	 * Get properties user data e.g. country, provinces, regency
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function getProperties(Request $request) 
	{
		$data = [
			'countries' => $this->engine->getCountries()
		];

		if( $request->input('country_id') ) {
			$data['provinces'] = $this->engine->getProvinces( $request->input('country_id') );
		}

		if( $request->input('province_id') ) {
			$data['regencies'] = $this->engine->getRegencies( $request->input('province_id') );
		}

		return response()->json($data, 200 );
	}

    /**
     * Validator
     *
     * @author Dian Afrial
     * @return void
     */
    public function userValidation($request)
    {
        $validator = Validator::make( 
            $request->only("name", "email"), 
            [
              'name'                    => 'required',
              'email'                   => 'nullable|email'
            ]
        );
        
        if( $validator->fails() ) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validator
     *
     * @author Dian Afrial
     * @return void
     */
    public function merchantValidation($request)
    {
        $validator = Validator::make( 
            $request->only("address", "country_id", "province_id", "regency_id", "open_at", "closed_at"), 
            [
              'address'                     => 'required',
              'country_id'                  => 'required|exists:countries,id',
              'province_id'                 => 'required|exists:provinces,id',
              'regency_id'                  => 'required|exists:regencies,id',
              'open_at'                     => 'required',
              'closed_at'                   => 'required'
            ]
        );
        
        if( $validator->fails() ) {
            throw new ValidationException($validator);
        }
    }

    protected function guard()
    {
        return Auth::guard();
    }

}
