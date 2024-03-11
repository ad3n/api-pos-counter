<?php

namespace App\Http\Controllers\API\v2\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use App\Repositories\ProductRepository;
use Illuminate\Validation\Rule;
use App\Interfaces\Constants;
use Validator;
use Auth;

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

    /**
     * Get Product Categorized
     *
     * @author Dian Afrial
     * @return void
     */
    public function getCategorized(Request $request)
    {
        $this->engine->guard = $request->guard;
        $data = $this->engine->getCategorized($request);

        return response()->json($data, 200);
    }

    /**
     * Get Master category
     *
     * @author Dian Afrial
     * @return json
     */
    public function getMasterCategory(Request $request)
    {
        $this->engine->guard = $request->guard;
        $data = $this->engine->getCategories($request->all());

        return response()->json($data, 200);
    }

    /**
     * Get Master category
     *
     * @author Dian Afrial
     * @return json
     */
    public function getMerchantCategory(Request $request)
    {
        $this->engine->guard = $request->guard;
        $data = $this->engine->getCategoriesByMerchant($request->all());

        return response()->json($data, 200);
    }

    /**
     * Add Category Selection
     *
     * @author Dian Afrial
     * @return json
     */
    public function createSelection(Request $request)
    {
        try {
            $this->engine->guard = $request->guard;
            $data = $this->engine->creatAddSelection($request->all());
            return response()->json($data, 200);
        } catch (HttpException $th) {
            abort(400, $th->getMessage());
        }
    }

    /**
     * Add Category Selection
     *
     * @author Dian Afrial
     * @return json
     */
    public function updateSelection(Request $request)
    {
        try {
            $this->engine->guard = $request->guard;
            $data = $this->engine->creatAddSelection($request->all());
            return response()->json($data, 200);
        } catch (HttpException $th) {
            abort(400, $th->getMessage());
        }
    }

    /**
     * Validator
     *
     * @author Dian Afrial
     * @return void
     */
    public function productValidation($request)
    {
        $validator = Validator::make(
            $request->only("code", "name", "price", "category_id"),
            [
                'code'                    => 'nullable',
                'name'                    => 'required',
                'price'                   => 'required|integer|min:10',
                'category_id'             => 'required'
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validator
     *
     * @author Dian Afrial
     * @return void
     */
    public function editProductValidation($request)
    {
        $validator = Validator::make(
            $request->only("name", "price", "category_id"),
            [
                'code'                    => 'nullable',
                'name'                    => 'required',
                'price'                   => 'required|integer|min:10',
                'category_id'             => 'required'
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Barcode Search Validator
     *
     * @author Dian Afrial
     * @return void
     */
    public function barSearchValidation($request)
    {
        $validator = Validator::make(
            $request->only("code"),
            [
                'code'                    => 'required'
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
