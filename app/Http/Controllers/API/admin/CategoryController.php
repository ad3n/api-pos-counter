<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use App\Repositories\ProductRepository;
use Illuminate\Validation\Rule;
use App\Interfaces\Constants;
use Auth;
use Validator;

class CategoryController extends Controller implements Constants
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
    public function __construct(ProductRepository $engine)
    {
        $this->engine = $engine;
    }

    public function getAll(Request $request)
    {
        try {
            $this->engine->guard = $request->guard;
            // make process login
            $res = $this->engine->getCategoryList($request);

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    public function create(Request $request)
    {
        try {
            $this->validation($request);

            // make process login
            $res = $this->engine->createCategory($request);

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_validation_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // fetch first
            $model = $this->engine->fetchCategory($id);

            // validate fields
            $this->updateValidation($request);

            // make process login
            $res = $this->engine->updateCategory($model, $request);

            // if success throw 200 OK
            return response()->json($res, 200);
        } catch (ValidationException $e) {
            return response()->json(error_validation_json($e->errors()), $e->status);
        } catch (HttpException $e) {
            return response()->json(error_json($e->getMessage()), $e->getStatusCode());
        }
    }

    /**
     * Delete category
     *
     * @param Request $request
     * @return void
     */
    public function deleteCat(Request $request, $id)
    {
        try {
            // make process login
            $res = $this->engine->deleteCategory($id);

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
    public function validation($request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'              => 'required|string|min:1|unique:categories,name'
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
    public function updateValidation($request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'              => 'required|string|min:1'
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
