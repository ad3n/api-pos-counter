<?php

namespace App\Http\Controllers\API\Tenant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use App\Repositories\CustomerRepository;
use Illuminate\Validation\Rule;
use App\Interfaces\Constants;
use Auth;
use Validator;

class CustomerController extends Controller implements Constants
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
    public function __construct(CustomerRepository $engine)
    {
        $this->engine = $engine;
    }

    public function getAll(Request $request)
    {
        try {
            $this->engine->guard = $request->guard;
            // make process login
            $offset = $request->input("offset") ? $request->input("offset") : 0;
            $limit = $request->input("limit") ? $request->input("limit") : 20;
            $res = $this->engine->getCustomerList($request, $offset, $limit);

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    public function getOfTransaction(Request $request)
    {
        try {
            $this->engine->guard = $request->guard;
            // add new contact
            $res = $this->engine->fetchCustomerOfTransaction();
            // if success throw 200 OK
            return response()->json($res, 200);

        } catch (ValidationException $e) {
            return response()->json(error_validation_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    public function create(Request $request)
    {
        try {
            // make validation
            $this->validation($request);

            $this->engine->guard = $request->guard;
            // add new contact
            $res = $this->engine->addCustomer($request);
            // if success throw 200 OK
            return response()->json($res, 200);

        } catch (ValidationException $e) {
            return response()->json(error_validation_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    public function fetch(Request $request, $id)
    {
        try {
            $this->engine->guard = $request->guard;
            // add new supplier
            $res = $this->engine->fetchCustomer($id);
            // if success throw 200 OK
            return response()->json([
                'success'   => true,
                'data'      => $res
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(error_validation_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->engine->guard = $request->guard;
            // validate fields
            $this->validation($request);
            // make process login
            $res = $this->engine->updateCustomer($id, $request);
            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_validation_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    public function trash(Request $request, $id)
    {
        try {
            $this->engine->guard = $request->guard;
            // make process
            $model = $this->engine->fetchCustomer($id);

            if( $model->getCreditTransactions()->count() > 0 ) {
                $status = 400;
                $res = [
                    'success'  => false,
                    'messages' => 'Customer could not remove becasue have credits'
                ];
            } else {
                $model->forceDelete();
                $status = 200;
                $res = [
                    'success'  => true,
                    'messages' => 'Customer has successfully removed'
                ];
            }
            
            // if success throw 200 OK
            return response()->json($res, $status);

        } catch (ValidationException $e) {
            return response()->json(error_validation_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Make validation request
     *
     * @author Dian Afrial
     * @return \HttpException
     */
    public function validation($request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'       => 'required|string|min:1',
                'no_hp'      => 'required|string|min:6',
                'email'      => 'nullable|email'
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
