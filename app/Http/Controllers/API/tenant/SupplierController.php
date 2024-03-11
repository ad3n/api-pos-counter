<?php

namespace App\Http\Controllers\API\Tenant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use App\Repositories\SupplierRepository;
use Illuminate\Validation\Rule;
use App\Interfaces\Constants;
use Auth;
use Validator;

class SupplierController extends Controller implements Constants
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
    public function __construct(SupplierRepository $engine)
    {
        $this->engine = $engine;
    }

    public function getAll(Request $request)
    {
        try {
            $this->engine->guard = $request->guard;
            // make process login
            $res = $this->engine->getSupplierList($request);

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    public function createSupplier(Request $request)
    {
        try {
            // make validation
            $this->validation($request);

            $this->engine->guard = $request->guard;
            // add new supplier
            $res = $this->engine->createNew($request);

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_validation_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    public function getSupplier(Request $request, $id)
    {
        try {
            $this->engine->guard = $request->guard;
            // add new supplier
            $res = $this->engine->fetchOne($id);

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

    public function updateSupplier(Request $request, $id)
    {
        try {
            $this->engine->guard = $request->guard;

            // validate fields
            $this->validation($request);

            // make process login
            $res = $this->engine->update($id, $request->all());

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_validation_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    public function trashSupplier(Request $request, $id)
    {
        try {
            $this->engine->guard = $request->guard;
            // make proces
            $res = $this->engine->forceDelete($id, $request);

            // if success throw 200 OK
            return response()->json($res, 200);
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
                'name'              => 'required|string|min:1',
                'address'           => 'required|string|min:1',
                'phone'             => 'required|min:8|max:18',
                'sales_person'      => 'nullable|string',
                'sales_contact'     => 'nullable|string|min:8|max:18'
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
