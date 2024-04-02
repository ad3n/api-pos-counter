<?php

namespace App\Http\Controllers\API\Tenant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use App\Repositories\ProductRepository;
use Illuminate\Validation\Rule;
use App\Interfaces\Constants;

class BrandController extends Controller implements Constants
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
   * Get Brands
   *
   * @author Dian Afrial
   * @return json
   */
  public function getBrands(Request $request)
  {
    $this->engine->guard = $request->guard;
    $data = $this->engine->getBrandList($request->all());
    return response()->json($data, 200);
  }
}
