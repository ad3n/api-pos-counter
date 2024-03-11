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

class ProductController extends Controller implements Constants
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
   * Get Product Categorized
   *
   * @author Dian Afrial
   * @return void
   */
  public function getSearchBarcode(Request $request)
  {
    try {
      $this->engine->guard = $request->guard;
      // validate first
      $this->barSearchValidation($request);

      // get product
      $data = $this->engine->getSearchBarcode($request);

      return response()->json($data, 200);
    } catch (ValidationException $e) {
      return response()->json(error_json($e->errors()), $e->status);
    } catch (HttpException $e) {
      return response()->json(error_json($e->getMessage()), $e->getStatusCode());
    }
  }

  /**
   * Get Products
   *
   * @author Dian Afrial
   * @return json
   */
  public function getAll(Request $request)
  {
    $this->engine->guard = $request->guard;
    $data = $this->engine->getProducts($request->all());
    return response()->json($data, 200);
  }

  /**
   * Get Products with pagination
   *
   * @author Dian Afrial
   * @return json
   */
  public function getAllByPaginate(Request $request)
  {
    $this->engine->guard = $request->guard;
    $data = $this->engine->getProductsWithPagination($request->all());
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
   * Handle Update User
   *
   * @author Dian Afrial
   * @return void
   */
  public function postNewProduct(Request $request)
  {
    try {
      $this->engine->guard = $request->guard;
      // validate first
      $this->productValidation($request);

      // make process add product
      $res = $this->engine->addUserProduct($request);

      // if success throw 200 OK
      return response()->json($res, 200);
    } catch (ValidationException $e) {
      return response()->json(error_json($e->errors()), $e->status);
    } catch (HttpException $e) {
      return response()->json(error_json($e->getMessage()), $e->getStatusCode());
    }
  }

  /**
   * Handle Update Product
   *
   * @author Dian Afrial
   * @return void
   */
  public function editProduct(Request $request, $id)
  {
    try {
      $this->engine->guard = $request->guard;
      // validate first
      $this->productValidation($request);

      // make process add product
      $res = $this->engine->updateUserProduct($id, $request);

      // if success throw 200 OK
      return response()->json($res, 200);
    } catch (ValidationException $e) {
      return response()->json(error_json($e->errors()), $e->status);
    } catch (HttpException $e) {
      return response()->json(error_json($e->getMessage()), $e->getStatusCode());
    }
  }

  /**
   * Handle Trash Product
   *
   * @author Dian Afrial
   * @return void
   */
  public function trashProduct(Request $request, $id)
  {
    try {
      $this->engine->guard = $request->guard;
      // make process add product
      $res = $this->engine->deleteUserProduct($id, $request);

      // if success throw 200 OK
      return response()->json($res, 200);
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
