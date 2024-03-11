<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use App\Repositories\UserRepository;
use App\Repositories\SaldoRepository;
use Illuminate\Validation\Rule;
use Auth;
use Validator;

class MerchantController extends Controller
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
	 * __constuctor
	 *
	 * @author Dian Afrial
	 * @return void
	 */
	public function __construct(
		UserRepository $engine,
		SaldoRepository $saldo
	) {
		$this->saldo = $saldo;
		$this->engine = $engine;
	}

	/**
	 * Get dashboard
	 *
	 * @author Dian Afrial
	 * @return json
	 */
	public function getDashboard(Request $request)
	{
		try {
			$this->engine->guard = $request->guard;
			// make process login
			$res = $this->engine->getSummaryMerchants($request);

			// if success throw 200 OK
			return response()->json($res, 200);
		} catch (ValidationException $e) {
			return response()->json(error_json($e->errors()), $e->status);
		} catch (HttpException $e) {
			return response()->json(error_json($e->getMessage()), $e->getStatusCode());
		}
	}

	/**
	 * Get all merchants
	 *
	 * @author Dian Afrial
	 * @return json
	 */
	public function getAll(Request $request)
	{
		try {
			// make process login
			$res = $this->engine->getMerchants($request);

			// if success throw 200 OK
			return response()->json($res, 200);
		} catch (ValidationException $e) {
			return response()->json(error_json($e->errors()), $e->status);
		} catch (HttpException $e) {
			return response()->json(error_json($e->getMessage()), $e->getStatusCode());
		}
	}

	/**
	 * Get merchant detail
	 *
	 * @author Dian Afrial
	 * @return json
	 */
	public function getDetail(Request $request, $id)
	{
		try {
			// make process login
			$res = $this->engine->getMerchantDetail($id, $request);

			// if success throw 200 OK
			return response()->json($res, 200);
		} catch (ValidationException $e) {
			return response()->json(error_json($e->errors()), $e->status);
		} catch (HttpException $e) {
			return response()->json(error_json($e->getMessage()), $e->getStatusCode());
		}
	}

	/**
	 * Update merchant
	 *
	 * @param Request $request
	 * @param int $id
	 * @return void
	 */
	public function updateMerchant(Request $request, $id)
	{
		try {
			// fetch first
			$model = $this->engine->fetchMerchant($id);

			// validate fields
			$this->validationMerchant($request, $model);

			// make process login
			$res = $this->engine->updateMerchant($model, $request);

			// if success throw 200 OK
			return response()->json($res, 200);
		} catch (ValidationException $e) {
			return response()->json(error_validation_json($e->errors()), $e->status);
		} catch (HttpException $e) {
			return response()->json(error_json($e->getMessage()), $e->getStatusCode());
		}
	}

	public function updateUserActive(Request $request, $id)
	{
		try {
			// fetch first
			$model = $this->engine->fetchUserMerchant($id);

			// validate fields
			$this->validationActivation($request);

			// make process
			$res = $this->engine->updateActiveUser($model, $request->input('state'));

			// if success throw 200 OK
			return response()->json($res, 200);
		} catch (ValidationException $e) {
			return response()->json(error_validation_json($e->errors()), $e->status);
		} catch (HttpException $e) {
			return response()->json(error_json($e->getMessage()), $e->getStatusCode());
		}
	}

	/**
	 * Get merchant types
	 *
	 * @author Dian Afrial
	 * @return json
	 */
	public function getMerchantTypes(Request $request, $type = 'dropdown')
	{
		try {
			if ($type == 'dropdown') {
				$res = $this->engine->getMerchantTypes($request);
			} else if ($type == 'all') {
				$res = $this->engine->fetchMerchantTypes($request);
			} else {
				abort(400, 'No operation exists');
			}

			// if success throw 200 OK
			return response()->json($res, 200);
		} catch (ValidationException $e) {
			return response()->json(error_json($e->errors()), $e->status);
		} catch (HttpException $e) {
			return response()->json(error_json($e->getMessage()), $e->getStatusCode());
		}
	}

	public function createType(Request $request)
	{
		try {
			$this->validationType($request);

			// make process login
			$res = $this->engine->createMerchantType($request);

			// if success throw 200 OK
			return response()->json($res, 200);
		} catch (ValidationException $e) {
			return response()->json(error_validation_json($e->errors()), $e->status);
		} catch (HttpException $e) {
			return response()->json(error_json($e->getMessage()), $e->getStatusCode());
		}
	}

	public function updateType(Request $request, $id)
	{
		try {
			// fetch first
			$model = $this->engine->fetchMerchantType($id);

			// validate fields
			$this->updateValidationType($request, $model);

			// make process login
			$res = $this->engine->updateMerchantType($model, $request);

			// if success throw 200 OK
			return response()->json($res, 200);
		} catch (ValidationException $e) {
			return response()->json(error_validation_json($e->errors()), $e->status);
		} catch (HttpException $e) {
			return response()->json(error_json($e->getMessage()), $e->getStatusCode());
		}
	}

	/**
	 * Delete user
	 *
	 * @param Request $request
	 * @return void
	 */
	public function deleteUser(Request $request, $id)
	{
		try {
			// make process login
			$res = $this->engine->deleteUserMerchant($id);

			// if success throw 200 OK
			return response()->json($res, 200);
		} catch (HttpException $e) {
			return response()->json(error_json($e->getMessage()), $e->getStatusCode());
		}
	}

	/**
	 * Delete merchant type
	 *
	 * @param Request $request
	 * @return void
	 */
	public function deleteType(Request $request, $code)
	{
		try {
			// make process login
			$res = $this->engine->deleteMerchantType($code);

			// if success throw 200 OK
			return response()->json($res, 200);
		} catch (HttpException $e) {
			return response()->json(error_json($e->getMessage()), $e->getStatusCode());
		}
	}

	/**
	 * Make validation reeuest
	 *
	 * @author Dian Afrial
	 * @return \HttpException
	 */
	public function validationMerchant($request)
	{
		$validator = Validator::make(
			$request->all(),
			[
				'name'              => 'required|string',
				'merchant_type'		=> 'required|exists:merchant_types,code',
				'address'			=> 'required'
			]
		);

		if ($validator->fails()) {
			throw new ValidationException($validator);
		}
	}

	/**
	 * Make validation reeuest
	 *
	 * @author Dian Afrial
	 * @return \HttpException
	 */
	public function validationType($request)
	{
		$validator = Validator::make(
			$request->all(),
			[
				'name'              => 'required|string|min:1|unique:merchant_types,name'
			]
		);

		if ($validator->fails()) {
			throw new ValidationException($validator);
		}
	}

	/**
	 * Make validation reeuest
	 *
	 * @author Dian Afrial
	 * @return \HttpException
	 */
	public function updateValidationType($request, $model)
	{
		$validator = Validator::make(
			$request->all(),
			[
				'name'             => [
					'required',
					'string',
					'min:1',
					Rule::unique('merchant_types')->ignore($model->code, 'code'),
				],
			]
		);

		if ($validator->fails()) {
			throw new ValidationException($validator);
		}
	}

	/**
	 * Make validation reeuest
	 *
	 * @author Dian Afrial
	 * @return \HttpException
	 */
	public function validationActivation($request)
	{
		$validator = Validator::make(
			$request->all(),
			[
				'state'              => ['required', Rule::in(['yes', 'no'])]
			]
		);

		if ($validator->fails()) {
			throw new ValidationException($validator);
		}
	}

	protected function guard()
	{
		return Auth::guard();
	}
}
