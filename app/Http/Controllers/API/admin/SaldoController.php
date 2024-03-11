<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use App\Repositories\SaldoCacheRepository;
use App\Repositories\SaldoRepository;
use Illuminate\Validation\Rule;
use App\Interfaces\Constants;
use Auth;
use Validator;

class SaldoController extends Controller implements Constants
{
    /**
     * Cache Repository
     *
     * @author Dian Afrial
     * @return object
     */
    protected $cache;

    /** Saldo Repository */
    protected $saldo;

    /**
     * __constuctor
     *
     * @author Dian Afrial
     * @return void
     */
    public function __construct(
        SaldoRepository $saldo,
        SaldoCacheRepository $cache
    ) {
        $this->saldo = $saldo;
        $this->cache = $cache;
    }

    public function getProperties(Request $request)
    {
        try {
            $this->engine->guard = $request->guard;

            $no = $this->cache->createTopupToken();

            $data = [
                'receipt_no'    => $no,
                'type' => [
                    static::SALDO_TYPE_FREE => __("user.saldo_type.free"),
                    static::SALDO_TYPE_DEPOSIT => __("user.saldo_type.deposit")
                ],
                'payment_method' => [
                    static::PAYMENT_METHOD_CASH     => __("user.payment_methods.cash"),
                    static::PAYMENT_METHOD_BANK     => __("user.payment_methods.bank_transfer"),
                    static::PAYMENT_METHOD_BONUS    => __("user.payment_methods.free"),
                ]
            ];

            // if success throw 200 OK
            return response()->json($data, 200);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    public function getMerchantSaldo(Request $request, $id)
    {
        try {
            $this->engine->guard = $request->guard;
            // Retrieve list
            $list = $this->saldo->getSaldoMerchant($id);

            // if success throw 200 OK
            return response()->json($list, 200);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Top-up saldo
     *
     * @author Dian Afrial
     * @return json
     */
    public function topupSaldo(Request $request, $id)
    {
        try {
            // Validate first
            $this->validation($request);

            // make process login
            $res = $this->saldo->topup($request, $id);

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
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
    public function validation($request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'amount'            => 'required|int|min:1',
                'merchant_id'       => 'required|exists:merchants,id',
                'type'              => ['required', Rule::in([
                    static::SALDO_TYPE_FREE,
                    static::SALDO_TYPE_DEPOSIT
                ])],
                'method'    => ['required', Rule::in([
                    static::PAYMENT_METHOD_CASH,
                    static::PAYMENT_METHOD_BANK,
                    static::PAYMENT_METHOD_BONUS
                ])],
                'note'              => 'nullable',
                'paid_date'           => 'required',
                'bank_name'         => 'nullable',
                'bank_code'         => 'nullable',
                'acc_holder'        => 'nullable',
                'acc_number'        => 'nullable',
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
