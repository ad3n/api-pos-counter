<?php

namespace App\Http\Controllers\API\v2\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use App\Repositories\TransactionRepository;
use Illuminate\Validation\Rule;
use App\Interfaces\Constants;
use App\Traits\Authentication;
use Validator;
use Auth;

class TransactionController extends Controller implements Constants
{
    /**
     * Engine Repository
     *
     * @author Dian Afrial
     * @return object
     */
    protected $engine;

    /**
     * __constuctor
     *
     * @author Dian Afrial
     * @return void
     */
    public function __construct(TransactionRepository $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Create transaction
     *
     * @author Dian Afrial
     * @return void
     */
    public function createTransaction(Request $request, $type)
    {
        try {
            $this->engine->guard = $request->guard;
            // check type transaction
            if ($type == 'omzet') {
                $this->validation($request);
                $res = $this->engine->omzetCreateTransaction($request);
            } else if ($type == 'expense') {
                $this->validationExpense($request);
                $res = $this->engine->expenseCreateTransaction($request);
            } else {
                return response()->json(error_json('No operation exists'), 400);
            }

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Add transaction
     *
     * @author Dian Afrial
     * @return void
     */
    public function addTransaction(Request $request)
    {
        try {
            $this->engine->guard = $request->guard;

            $validator = Validator::make(
                $request->only(['type', 'payment_status', 'payment_method', 'items']),
                [
                    'type'           => 'required',
                    'payment_status' => 'required',
                    'payment_method' => 'required',
                    'items'          => 'required|array|min:1'
                ]
            );

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $res = $this->engine->addTransaction($request);

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Get transaction Detail
     *
     * @param Request $request
     * @param string $order_no
     * @return void
     */
    public function getTransactionDetail(Request $request, $order_no)
    {
        try {
            $this->engine->guard = $request->guard;
            $res = $this->engine->getDetail($request, $order_no);
            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Transaction list by Merchnt ID
     *
     * @author Dian Afrial
     * @return void
     */
    public function transactionByType(Request $request, $type)
    {
        try {
            $this->engine->guard = $request->guard;
            $res = $this->engine->transactionListByDate($request, $type);
            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Transaction list by Merchnt ID
     *
     * @author Dian Afrial
     * @return void
     */
    public function userTransactionByMerchant(Request $request)
    {
        try {
            $this->engine->guard = $request->guard;
            // make process login

            if ($request->input("payment_status") == 'credit') {
                $res = $this->engine->transactionListByStatus($request);
            } else if ($request->input("payment_status") == 'paid') {
                $res = $this->engine->transactionListByStatus($request);
            } else {
                if ($request->input("date") && $request->input("date") == 'latest') {
                    $res = $this->engine->latestTransaction($request);
                } else if ($request->input("date") && $request->input("date") == 'all') {
                    $res = $this->engine->allTransaction($request);
                } else if ($request->input("date")) {
                    $res = $this->engine->transactionListByDate($request);
                } else {
                    return response()->json(error_json('Invalid requests'), 400);
                }
            }

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Edit transaction
     *
     * @param Request $request
     * @param string $order_no
     * @return void
     */
    public function editExpense(Request $request, $order_no)
    {
        try {
            $this->engine->guard = $request->guard;
            // validate update request
            $this->validationEditExpense($request);

            $res = $this->engine->updateExpense($request, $order_no);
            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Delete transaction
     *
     * @param Request $request
     * @param string $order_no
     * @return void
     */
    public function removeTransaction(Request $request, $order_no)
    {
        try {
            $this->engine->guard = $request->guard;

            $res = $this->engine->deleteTransaction($request, $order_no);
            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Edit transaction detail
     *
     * @param Request $request
     * @param string $order_no
     * @return void
     */
    public function editTransactionDetail(Request $request, $order_no)
    {
        try {
            $this->engine->guard = $request->guard;
            // validate update request
            $this->validatorDeleteUpdate($request);

            $res = $this->engine->updateTransactionItem($request, $order_no);
            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Edit transaction
     *
     * @param Request $request
     * @param string $order_no
     * @return void
     */
    public function editTransaction(Request $request, $order_no)
    {
        try {
            $this->engine->guard = $request->guard;
            // validate update request
            $this->validatorUpdate($request);

            $res = $this->engine->updateTransaction($request, $order_no);
            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }


    /**
     * Remove transaction detail
     *
     * @param Request $request
     * @param string $order_no
     * @return void
     */
    public function removeTransactionItem(Request $request, $order_no)
    {
        try {
            $this->engine->guard = $request->guard;
            // validate update request
            $this->validatorDeleteUpdate($request);

            $res = $this->engine->deleteTransactionItem($request, $order_no);
            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Set status to cash
     *
     * @param Request $request
     * @param string $order_no
     * @return void
     */
    public function paymentStatus(Request $request, $order_no)
    {
        try {
            $this->engine->guard = $request->guard;
            $this->validatorPaymentStatus($request);
            // validate update request
            $res = $this->engine->setPaymentStatus($request, $order_no);
            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Set status to cash
     *
     * @param Request $request
     * @param string $order_no
     * @return void
     */
    public function getWorkDate(Request $request, $order_no)
    {
        try {
            $this->engine->guard = $request->guard;
            // if success throw 200 OK
            return response()->json(['messages' => $this->getWorkDate()], 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Validator
     *
     * @author Dian Afrial
     * @return void
     */
    public function validation($request)
    {
        $validator = Validator::make(
            $request->only(['order_no', 'payment_status', 'payment_method', 'items']),
            [
                'order_no'       => 'required|unique:transactions,order_no',
                'payment_status' => 'required',
                'payment_method' => 'required',
                'items'          => 'required|array|min:1'
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validator of Expense
     *
     * @author Dian Afrial
     * @return void
     */
    public function validatorUpdate($request)
    {
        $validator = Validator::make(
            $request->only(['id', 'type', 'name']),
            [
                'name'      => 'required|string',
                'type'      => [
                    'required',
                    Rule::in([
                        static::TRANSACTION_TYPE_EXPENSE,
                        static::TRANSACTION_TYPE_OMZET
                    ])
                ]
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validator of Expense
     *
     * @author Dian Afrial
     * @return void
     */
    public function validationExpense($request)
    {
        $validator = Validator::make(
            $request->only(['name', 'price']),
            [
                'name'      => 'required|string',
                'price'     => 'required',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validator of Edit Expense
     *
     * @author Dian Afrial
     * @return void
     */
    public function validationEditExpense($request)
    {
        $validator = Validator::make(
            $request->only(['name', 'price']),
            [
                'name'      => 'required|string',
                'price'     => 'required',
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validator of Expense
     *
     * @author Dian Afrial
     * @return void
     */
    public function validatorDeleteUpdate($request)
    {
        $validator = Validator::make(
            $request->only(['id', 'type']),
            [
                'id'        => 'required|exists:transaction_items,id',
                'type'      => [
                    'required',
                    Rule::in([
                        static::TRANSACTION_TYPE_EXPENSE,
                        static::TRANSACTION_TYPE_OMZET
                    ])
                ]
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validator of Expense
     *
     * @author Dian Afrial
     * @return void
     */
    public function validatorPaymentStatus($request)
    {
        $validator = Validator::make(
            $request->only(['status']),
            [
                'status'      => [
                    'required',
                    Rule::in([
                        static::PAYMENT_STATUS_PAID,
                        static::PAYMENT_STATUS_CREDIT
                    ])
                ]
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
